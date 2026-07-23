<?php

namespace App\Services\PedidosWeb\CargaAsistente;

final class CargaAsistenteIntentDetector
{
    /**
     * Palabras/abreviaturas de renglón: artículo(s), art., producto(s), prod., item(s), it.
     * Orden: formas largas antes que art/it para no partir mal el match.
     */
    private const ARTICULO_KEYWORD_REGEX = 'articulos?|artículos?|productos?|items?|art\.?|prod\.?|it\.?';

    /**
     * @param  array<string, mixed>|null  $pendingChoice
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     * @return array{intent: string, params: array<string, mixed>}
     */
    public function detect(string $message, ?array $pendingChoice, array $normalizedImages = []): array
    {
        if ($normalizedImages !== []) {
            return [
                'intent' => 'applyImageExtract',
                'params' => ['message' => $message],
            ];
        }

        $normalized = $this->normalizeText($message);

        if ($pendingChoice !== null) {
            $kind = (string) ($pendingChoice['kind'] ?? '');

            if ($kind === 'changeClienteConfirm') {
                if ($this->matchesAny($normalized, ['si', 'sí', 'confirmo', 'aceptado'])) {
                    return ['intent' => 'confirmChangeCliente', 'params' => []];
                }

                if ($this->matchesAny($normalized, ['no', 'cancelar'])) {
                    return ['intent' => 'rejectChangeCliente', 'params' => []];
                }
            }

            if (
                in_array($kind, [
                    'needsChoice',
                    'cliente',
                    'articulo',
                    'transporte',
                    'condicionVenta',
                    'perfil',
                    'listaPrecios',
                    'direccionEntrega',
                    'renglonExistente',
                ], true)
                || isset($pendingChoice['options'])
            ) {
                if (preg_match('/^\s*([1-9]|10)\s*$/u', $normalized, $matches) === 1) {
                    return [
                        'intent' => 'chooseOption',
                        'params' => ['option' => (int) $matches[1]],
                    ];
                }
            }
        }

        $compositeItems = $this->detectCompositeItems($message);
        if (count($compositeItems) >= 2) {
            return [
                'intent' => 'compositePedido',
                'params' => ['items' => $compositeItems],
            ];
        }

        return $this->detectSingle($message, $normalized);
    }

    /**
     * @return array{intent: string, params: array<string, mixed>}
     */
    private function detectSingle(string $message, ?string $normalized = null): array
    {
        $normalized ??= $this->normalizeText($message);

        if (preg_match('/\b(cambiar\s+cliente|otro\s+cliente)\b/u', $normalized) === 1) {
            $q = $this->extractAfterPatterns($message, [
                '/cambiar\s+cliente\s+(?:a\s+|por\s+)?(.+)/iu',
                '/otro\s+cliente\s+(?:a\s+|por\s+)?(.+)/iu',
            ]);

            return [
                'intent' => 'changeCliente',
                'params' => ['q' => $q !== '' ? $q : $this->extractQueryAfterKeywords($message, ['cambiar cliente', 'otro cliente'])],
            ];
        }

        if (preg_match('/\b(cliente|buscar\s+cliente)\b/u', $normalized) === 1) {
            $q = $this->extractAfterPatterns($message, [
                '/buscar\s+cliente\s*[:=]?\s*(.+)/iu',
                '/cliente\s*[:=]?\s*(.+)/iu',
            ]);
            if ($q === '') {
                $q = $this->extractQueryAfterKeywords($message, ['buscar cliente', 'cliente']);
            }

            return [
                'intent' => 'selectCliente',
                'params' => ['q' => $this->sanitizeFieldValue($q)],
            ];
        }

        if (preg_match(
            '/\b(eliminar|elimina|eliminá|borrar|borra|borrá|quitar|quita|quitá|sacar|saca|sacá)\b/u',
            $normalized,
        ) === 1) {
            $params = $this->extractArticuloParams($message);
            $q = $this->extractMutateArticuloQuery($message, array_merge(
                [
                    'eliminar articulo',
                    'eliminar artículo',
                    'elimina articulo',
                    'elimina artículo',
                    'eliminá articulo',
                    'eliminá artículo',
                    'borrar articulo',
                    'borrar artículo',
                    'borra articulo',
                    'borra artículo',
                    'quitar articulo',
                    'quitar artículo',
                    'quita articulo',
                    'quita artículo',
                    'sacar articulo',
                    'sacar artículo',
                    'saca articulo',
                    'saca artículo',
                    'eliminar item',
                    'elimina item',
                    'borrar item',
                    'quita item',
                    'sacar item',
                    'eliminar art',
                    'elimina art',
                    'borrar art',
                    'quita art',
                ],
                $this->articuloKeywordList(),
                [
                    'eliminar',
                    'elimina',
                    'eliminá',
                    'borrar',
                    'borra',
                    'borrá',
                    'quitar',
                    'quita',
                    'quitá',
                    'sacar',
                    'saca',
                    'sacá',
                ],
            ));
            if ($q === '') {
                $q = (string) ($params['q'] ?? '');
            }
            $q = $this->stripLeadingArticles($q);

            return [
                'intent' => 'mutateRenglon',
                'params' => [
                    'operation' => 'remove',
                    'q' => $q,
                    'ultimo' => preg_match('/\b(ultimo|ultima)\s+rengl[oó]n\b/u', $normalized) === 1,
                ],
            ];
        }

        $ultimoRenglon = preg_match('/\b(ultimo|ultima)\s+rengl[oó]n\b/u', $normalized) === 1;
        $pideMutarAccion = preg_match('/\b(modificar|cambiar|actualizar|poner)\b/u', $normalized) === 1;
        $pideMutarValores = preg_match(
            '/\b(cantidad|precio|bonificaci[oó]n|bonif|descuento|desc\.?|dto)\b/u',
            $normalized,
        ) === 1;
        $hablaDeArticuloOrRenglon = preg_match(
            '/\b(?:'.self::ARTICULO_KEYWORD_REGEX.'|rengl[oó]n)\b/u',
            $normalized,
        ) === 1;

        // Evitar capturar altas tipo “articulo X cantidad 10 precio 150” como update.
        if (
            $ultimoRenglon
            || ($pideMutarAccion && $pideMutarValores && ($ultimoRenglon || $hablaDeArticuloOrRenglon))
        ) {
            $params = $this->extractArticuloParams($message);
            $explicitCantidad = preg_match(
                '/\b(?:cantidad|canti|cant\.?|unidades?|uds?)\b|\b\d+(?:[.,]\d+)?\s*(?:unidades?|uds?)\b/u',
                $normalized,
            ) === 1;

            // Preferir “cantidad … a 5” / “cantidad: 5” sobre el default 1 de extractArticuloParams.
            $cantidad = null;
            if (preg_match('/\bcantidad\b(?:\s+\S+){0,8}\s+(?:a|=|:)\s*(-?\d+(?:[.,]\d+)?)/iu', $message, $cantMatch) === 1) {
                $cantidad = (float) str_replace(',', '.', $cantMatch[1]);
                $explicitCantidad = true;
            } elseif ($explicitCantidad) {
                $cantidad = $params['cantidad'];
            }

            $q = '';
            if (! $ultimoRenglon) {
                $q = $this->extractMutateArticuloQuery($message, array_merge(
                    [
                        'modificar articulo',
                        'modificar artículo',
                        'cambiar articulo',
                        'cambiar artículo',
                        'actualizar articulo',
                        'actualizar artículo',
                        'modificar item',
                        'cambiar item',
                        'actualizar item',
                        'modificar art',
                        'cambiar art',
                    ],
                    $this->articuloKeywordList(),
                    [
                        'renglon',
                        'renglón',
                    ],
                ));
                if ($q === '' || $q === trim($message)) {
                    $q = (string) ($params['q'] ?? '');
                }
            }

            return [
                'intent' => 'mutateRenglon',
                'params' => [
                    'operation' => 'update',
                    'q' => $q,
                    'ultimo' => $ultimoRenglon,
                    'cantidad' => $explicitCantidad ? $cantidad : null,
                    'precio' => $params['precio'],
                    'porcBonif' => $params['porcBonif'],
                ],
            ];
        }

        if ($this->looksLikeAddArticulo($normalized)) {
            return [
                'intent' => 'addRenglon',
                'params' => $this->extractArticuloParams($message),
            ];
        }

        if (preg_match('/\bstock\b/u', $normalized) === 1) {
            return [
                'intent' => 'consultaStock',
                'params' => [
                    'q' => $this->extractQueryAfterKeywords($message, ['stock']),
                ],
            ];
        }

        if (preg_match('/\bdeuda\b/u', $normalized) === 1) {
            return ['intent' => 'consultaDeuda', 'params' => []];
        }

        if (preg_match('/\bcheque(s)?\b/u', $normalized) === 1) {
            return ['intent' => 'consultaCheques', 'params' => []];
        }

        if (preg_match('/\b(historial|ventas)\b/u', $normalized) === 1) {
            return ['intent' => 'consultaHistorial', 'params' => []];
        }

        if (preg_match('/\bgrabar\s+pedido\b/u', $normalized) === 1) {
            return ['intent' => 'grabarPedido', 'params' => []];
        }

        if (preg_match('/\bgrabar\s+presupuesto\b/u', $normalized) === 1) {
            return ['intent' => 'grabarPresupuesto', 'params' => []];
        }

        if (preg_match('/\btransporte\b/u', $normalized) === 1) {
            return [
                'intent' => 'setTransporte',
                'params' => [
                    'q' => $this->extractCampoLibreValue($message, ['transporte']),
                ],
            ];
        }

        if (preg_match('/\b(condicion\s+de\s+venta|condicion\s+venta|condici[oó]n\s+de\s+venta)\b/u', $normalized) === 1) {
            return [
                'intent' => 'setCondicionVenta',
                'params' => [
                    'q' => $this->extractCampoLibreValue($message, [
                        'condicion de venta',
                        'condición de venta',
                        'condicion venta',
                        'condición venta',
                    ]),
                ],
            ];
        }

        if (preg_match('/\bperfil\b/u', $normalized) === 1) {
            return [
                'intent' => 'setPerfil',
                'params' => [
                    'q' => $this->extractCampoLibreValue($message, ['perfil']),
                ],
            ];
        }

        // Evitar que “Leyenda 1: entregar lista de precios” dispare lista de precios.
        if (
            preg_match('/\b(lista\s+de\s+precios|lista\s+precios)\b/u', $normalized) === 1
            && preg_match('/^(?:leyenda|observacion|bonificaci|bonif|descuento|descto|desc\.?|dto\.?)\b/u', $normalized) !== 1
        ) {
            return [
                'intent' => 'setListaPrecios',
                'params' => [
                    'q' => $this->extractCampoLibreValue($message, [
                        'lista de precios',
                        'lista precios',
                    ]),
                ],
            ];
        }

        if (preg_match('/\b(fecha\s+de\s+entrega|fecha\s+entrega)\b/u', $normalized) === 1) {
            return [
                'intent' => 'setFechaEntrega',
                'params' => [
                    'value' => $this->extractCampoLibreValue($message, [
                        'fecha de entrega',
                        'fecha entrega',
                    ]),
                ],
            ];
        }

        if (preg_match('/\b(direccion\s+de\s+entrega|direcci[oó]n\s+de\s+entrega|direccion\s+entrega)\b/u', $normalized) === 1) {
            return [
                'intent' => 'setDireccionEntrega',
                'params' => [
                    'q' => $this->extractCampoLibreValue($message, [
                        'direccion de entrega',
                        'dirección de entrega',
                        'direccion entrega',
                        'dirección entrega',
                    ]),
                ],
            ];
        }

        if (preg_match('/\bobservacion(es)?\b/u', $normalized) === 1) {
            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'observaciones',
                    'value' => $this->extractCampoLibreValue($message, ['observaciones', 'observacion', 'observación']),
                ],
            ];
        }

        if (preg_match('/\bnivel\b/u', $normalized) === 1) {
            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'nivel',
                    'value' => $this->extractCampoLibreValue($message, ['nivel']),
                ],
            ];
        }

        if (preg_match('/\bleyenda\s*([1-5])\b/u', $normalized, $matches) === 1) {
            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'leyenda'.(int) $matches[1],
                    'value' => $this->extractCampoLibreValue($message, ['leyenda '.$matches[1], 'leyenda'.$matches[1]]),
                ],
            ];
        }

        // Cabecera: bonificación / descuento 1/2/3 (antes que renglón genérico “bonificacion N%”).
        if (preg_match(
            '/\b(?:bonificaci[oó]n|bonif|descuento|descto|desc\.?|dto\.?)\s*([123])\b/u',
            $normalized,
            $matches,
        ) === 1) {
            $slot = (int) $matches[1];

            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'bonif'.$slot,
                    'value' => $this->extractCampoLibreValue($message, [
                        'bonificacion '.$slot,
                        'bonificación '.$slot,
                        'bonif '.$slot,
                        'bonificacion'.$slot,
                        'bonificación'.$slot,
                        'bonif'.$slot,
                        'descuento '.$slot,
                        'descto '.$slot,
                        'desc '.$slot,
                        'dto '.$slot,
                        'descuento'.$slot,
                        'descto'.$slot,
                    ]),
                ],
            ];
        }

        // “dirección expreso” / “expreso dire” → expresoDire (antes que “expreso” solo).
        if (preg_match(
            '/\b(?:direcci[oó]n\s+expreso|expreso\s+dire(?:cci[oó]n)?|expreso[_ ]?dire)\b/u',
            $normalized,
        ) === 1) {
            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'expresoDire',
                    'value' => $this->extractCampoLibreValue($message, [
                        'direccion expreso',
                        'dirección expreso',
                        'expreso direccion',
                        'expreso dirección',
                        'expreso dire',
                        'expreso_dire',
                        'expresoDire',
                    ]),
                ],
            ];
        }

        if (preg_match('/\bexpreso\b/u', $normalized) === 1) {
            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'expreso',
                    'value' => $this->extractCampoLibreValue($message, ['expreso']),
                ],
            ];
        }

        // “Direccion: …” suelta = dirección del expreso (texto libre); “dirección de entrega” ya se resolvió arriba.
        if (preg_match('/\bdirecci[oó]n\b/u', $normalized) === 1) {
            return [
                'intent' => 'setCampoLibre',
                'params' => [
                    'field' => 'expresoDire',
                    'value' => $this->extractCampoLibreValue($message, ['direccion', 'dirección']),
                ],
            ];
        }

        return [
            'intent' => 'unknown',
            'params' => [],
        ];
    }

    /**
     * Pedido pegado con varias líneas etiqueta:valor + artículos → una intención por línea.
     * También admite un solo renglón (p. ej. dictado) con varias palabras clave.
     *
     * @return list<array{intent: string, params: array<string, mixed>}>
     */
    private function detectCompositeItems(string $message): array
    {
        $segments = $this->splitCompositeSegments($message);
        if (count($segments) < 2) {
            return [];
        }

        $items = [];
        foreach ($segments as $segment) {
            $detected = $this->detectSingle($segment);
            if (($detected['intent'] ?? 'unknown') === 'unknown') {
                continue;
            }
            $items[] = $detected;
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    private function splitCompositeSegments(string $message): array
    {
        $lines = preg_split('/\R/u', $message) ?: [];
        $segments = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($this->lineLooksLikeLabeledFieldOrArticulo($trimmed)) {
                $segments[] = $trimmed;
                continue;
            }

            if ($segments !== []) {
                $segments[count($segments) - 1] .= ' '.$trimmed;
            }
        }

        // Una sola línea (dictado / pegado plano): partir por palabras clave internas.
        if (count($segments) <= 1) {
            $flat = count($segments) === 1 ? $segments[0] : trim($message);
            if ($flat === '') {
                return [];
            }

            return $this->splitByInlineKeywords($flat);
        }

        return $segments;
    }

    /**
     * Parte un texto continuo en segmentos iniciados por etiqueta/cliente/artículo.
     *
     * @return list<string>
     */
    private function splitByInlineKeywords(string $text): array
    {
        $markerPattern = $this->inlineSegmentMarkerPattern();
        if (preg_match_all($markerPattern, $text, $matches, PREG_OFFSET_CAPTURE) < 1) {
            return [trim($text)];
        }

        /** @var list<array{0: string, 1: int}> $found */
        $found = $matches[0];
        if (count($found) < 2) {
            return [trim($text)];
        }

        $segments = [];
        $count = count($found);
        for ($index = 0; $index < $count; $index += 1) {
            $start = (int) $found[$index][1];
            $end = $index + 1 < $count ? (int) $found[$index + 1][1] : strlen($text);
            $segment = trim(substr($text, $start, $end - $start));
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return $segments !== [] ? $segments : [trim($text)];
    }

    private function inlineSegmentMarkerPattern(): string
    {
        // No incluir bonificación/descuento N sin “:” (en renglón suele ser % del artículo).
        return '/(?:^|(?<=\s))(?:'
            .'buscar\s+cliente|cliente|'
            .'(?:'.self::ARTICULO_KEYWORD_REGEX.')|'
            .'perfil|'
            .'condicion(?:\s+de)?\s+venta|condici[oó]n(?:\s+de)?\s+venta|'
            .'fecha(?:\s+de)?\s+entrega|'
            .'transporte|expreso|'
            .'direccion(?:\s+de\s+entrega|\s+expreso)?|direcci[oó]n(?:\s+de\s+entrega|\s+expreso)?|'
            .'lista(?:\s+de)?\s+precios|'
            .'(?:bonificaci[oó]n|bonif|descuento|descto|desc\.?|dto\.?)\s*[123]\s*:|'
            .'leyenda\s*[1-5]|observacion(?:es)?|nivel'
            .')\b/iu';
    }

    private function lineLooksLikeLabeledFieldOrArticulo(string $line): bool
    {
        $normalized = $this->normalizeText($line);

        if ($this->looksLikeAddArticulo($normalized)) {
            return true;
        }

        return preg_match(
            '/^(?:'
            .'cliente|perfil|'
            .'condicion(?:\s+de)?\s+venta|condici[oó]n(?:\s+de)?\s+venta|'
            .'fecha(?:\s+de)?\s+entrega|transporte|expreso|'
            .'direccion(?:\s+de\s+entrega|\s+expreso)?|direcci[oó]n(?:\s+de\s+entrega|\s+expreso)?|'
            .'lista(?:\s+de)?\s+precios|'
            .'(?:bonificaci[oó]n|bonif|descuento|descto|desc\.?|dto\.?)\s*[123]|'
            .'leyenda\s*[1-5]|observacion(?:es)?|nivel'
            .')\b/u',
            $normalized,
        ) === 1;
    }

    private function sanitizeFieldValue(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^([^\r\n]+)/u', $value, $matches) === 1) {
            $value = trim($matches[1]);
        }

        // Cortar ante artículo/producto/item (dictado en una línea: “cliente X artículo Y…”).
        $cutArticulo = preg_split(
            '/\s+(?:'.self::ARTICULO_KEYWORD_REGEX.')\b/iu',
            $value,
            2,
        );
        $value = trim((string) ($cutArticulo[0] ?? $value));

        // Otras etiquetas de cabecera: solo con “:” (evita partir “entregar lista de precios”).
        $cut = preg_split(
            '/\s+(?:perfil|condicion(?:\s+de)?\s+venta|condici[oó]n(?:\s+de)?\s+venta|fecha(?:\s+de)?\s+entrega|transporte|expreso|direccion(?:\s+de\s+entrega|\s+expreso)?|direcci[oó]n(?:\s+de\s+entrega|\s+expreso)?|lista(?:\s+de)?\s+precios|(?:bonificaci[oó]n|bonif|descuento|descto)\s*[123]|leyenda\s*[1-5]|observacion(?:es)?|nivel|cliente)\s*:/iu',
            $value,
            2,
        );

        return trim((string) ($cut[0] ?? $value));
    }

    private function normalizeText(string $message): string
    {
        $normalized = mb_strtolower(trim($message));
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $normalized);

        return preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
    }

    /**
     * @param  list<string>  $tokens
     */
    private function matchesAny(string $normalized, array $tokens): bool
    {
        foreach ($tokens as $token) {
            $candidate = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], mb_strtolower($token));
            if ($normalized === $candidate || preg_match('/^'.preg_quote($candidate, '/').'\b/u', $normalized) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $patterns
     */
    private function extractAfterPatterns(string $message, array $patterns): string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches) === 1) {
                return trim((string) ($matches[1] ?? ''));
            }
        }

        return '';
    }

    /**
     * Localiza el artículo: comillas (\"…\" o '…') o el texto después de la palabra clave
     * (convención: descripción al final del mensaje).
     *
     * @param  list<string>  $keywords
     */
    private function extractMutateArticuloQuery(string $message, array $keywords): string
    {
        if (preg_match('/["“”]([^"“”]+)["“”]/u', $message, $quoted) === 1) {
            return trim($quoted[1]);
        }

        if (preg_match("/'([^']+)'/u", $message, $quoted) === 1) {
            return trim($quoted[1]);
        }

        return $this->extractQueryAfterKeywords($message, $keywords);
    }

    private function stripLeadingArticles(string $q): string
    {
        $q = trim($q);
        $q = preg_replace('/^(el|la|los|las|un|una|unos|unas)\s+/iu', '', $q) ?? $q;

        return trim($q);
    }

    /**
     * @param  list<string>  $keywords
     */
    private function extractQueryAfterKeywords(string $message, array $keywords): string
    {
        foreach ($keywords as $keyword) {
            $escaped = preg_quote($keyword, '/');
            // Palabra completa (evita que “it”/“art” partan “item”/“articulo”).
            if (preg_match('/(?:^|[^\p{L}\p{N}])'.$escaped.'(?=[^\p{L}\p{N}]|$)/iu', $message, $matches, PREG_OFFSET_CAPTURE) !== 1) {
                continue;
            }

            $matchOffset = (int) $matches[0][1];
            $matchLength = strlen($matches[0][0]);
            $after = trim(substr($message, $matchOffset + $matchLength));

            // No comer el '-' de números negativos (p. ej. bonificación 3 -2).
            return preg_replace('/^[:\s]+/u', '', $after) ?? $after;
        }

        return trim($message);
    }

    private function looksLikeAddArticulo(string $normalized): bool
    {
        if (preg_match('/\b(?:'.self::ARTICULO_KEYWORD_REGEX.'|agregar|cargar)\b/u', $normalized) === 1) {
            return true;
        }

        // Ej.: "almendra carmel 10 unidades 120 $"
        if (preg_match('/\b\d+(?:[.,]\d+)?\s*(?:unidades?|uds?|u\.?)\b/u', $normalized) === 1) {
            return true;
        }

        if (preg_match('/\$\s*\d+(?:[.,]\d+)?|\b\d+(?:[.,]\d+)?\s*\$/u', $normalized) === 1) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function articuloKeywordList(): array
    {
        return [
            'articulos',
            'artículos',
            'articulo',
            'artículo',
            'productos',
            'producto',
            'items',
            'item',
            'art.',
            'art',
            'prod.',
            'prod',
            'it.',
            'it',
        ];
    }

    /**
     * @return array{q: string, cantidad: float, precio: float|null, porcBonif: float|null}
     */
    private function extractArticuloParams(string $message): array
    {
        $cantidad = 1.0;
        $precio = null;
        $porcBonif = null;
        $working = $message;

        if (preg_match('/(?:cantidad|canti|cant\.?|x)\s*[:=]?\s*(\d+(?:[.,]\d+)?)/iu', $working, $matches) === 1) {
            $cantidad = (float) str_replace(',', '.', $matches[1]);
            $working = trim(str_replace($matches[0], '', $working));
        } elseif (preg_match('/\b(\d+(?:[.,]\d+)?)\s*(?:unidades?|uds?|u\.?)\b/iu', $working, $matches) === 1) {
            $cantidad = (float) str_replace(',', '.', $matches[1]);
            $working = trim(str_replace($matches[0], '', $working));
        }

        if (preg_match('/\bprecio\b\s*[:=]?\s*\$?\s*(\d+(?:[.,]\d+)?)/iu', $working, $matches) === 1) {
            $precio = (float) str_replace(',', '.', $matches[1]);
            $working = trim(str_replace($matches[0], '', $working));
        } elseif (preg_match('/\$\s*(\d+(?:[.,]\d+)?)|(\d+(?:[.,]\d+)?)\s*\$/u', $working, $matches) === 1) {
            $rawPrecio = (string) ($matches[1] ?? '');
            if ($rawPrecio === '') {
                $rawPrecio = (string) ($matches[2] ?? '');
            }
            $precio = (float) str_replace(',', '.', $rawPrecio);
            $working = trim(str_replace($matches[0], '', $working));
        }

        // bonificación / bonificacion / bonif / bon. / descuento / desc. / dto. + % opcional
        if (preg_match(
            '/\b(?:bonificaci[oó]n|bonif\.?|bon\.?|descuento|desc\.?|dto\.?)\b\s*[:=]?\s*(-?\d+(?:[.,]\d+)?)\s*%?/iu',
            $working,
            $matches,
        ) === 1) {
            $porcBonif = (float) str_replace(',', '.', $matches[1]);
            $working = trim(str_replace($matches[0], '', $working));
        }

        // Preferir descripción entre comillas.
        if (preg_match('/["“”\']([^"“”\']+)["“”\']/u', $working, $quoted) === 1) {
            $q = trim($quoted[1]);
        } else {
            $q = $this->extractAfterPatterns($working, [
                '/agregar\s+(?:'.self::ARTICULO_KEYWORD_REGEX.')?\s*(.+)/iu',
                '/(?:'.self::ARTICULO_KEYWORD_REGEX.')\s+(.+)/iu',
                '/cargar\s+(.+)/iu',
            ]);

            if ($q === '') {
                $q = $this->extractQueryAfterKeywords(
                    $working,
                    array_merge(['agregar', 'cargar'], $this->articuloKeywordList()),
                );
            }

            if ($q === '' || $q === trim($message)) {
                $q = trim($working);
            }
        }

        $q = trim($q, " \t\"'`");
        $q = trim(preg_replace('/\s+/u', ' ', $q) ?? $q);

        return [
            'q' => $q,
            'cantidad' => $cantidad > 0 ? $cantidad : 1.0,
            'precio' => $precio !== null && $precio >= 0 ? $precio : null,
            'porcBonif' => $porcBonif !== null ? $porcBonif : null,
        ];
    }

    /**
     * @param  list<string>  $keywords
     */
    private function extractCampoLibreValue(string $message, array $keywords): string
    {
        return $this->sanitizeFieldValue($this->extractQueryAfterKeywords($message, $keywords));
    }
}
