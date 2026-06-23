<?php

namespace App\Services\ChatAssistant;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ChatAssistantCorpusResolver
{
    private const DOCS_FOLDER_NAME = 'docs';

    /**
     * @var list<string>
     */
    private const APPROVED_ROOTS = [
        '99-manual-usuario',
        '02-producto/PedidosWeb',
    ];

    /**
     * @var list<string>
     */
    private const EXCLUDED_PATH_FRAGMENTS = [
        '03-historias-usuario',
        '04-tareas',
        '05-open-spec',
        '00-contexto/_mono/00-arquitectura',
        'PedidosWeb_Definicion_Conceptual',
        'PedidosWeb_Plan_Cursor',
        'PedidosWeb_Scaffold_Inicio',
        'PedidosWeb_Modelo_Datos',
    ];

    /**
     * @var list<string>
     */
    private const STOPWORDS = [
        'por', 'que', 'los', 'las', 'del', 'una', 'uno', 'con', 'para', 'sus', 'son',
        'como', 'esta', 'este', 'pero', 'mas', 'muy', 'sin', 'sobre', 'puede', 'tiene',
        'tienen', 'estan', 'ese', 'esa', 'esos', 'esas', 'the', 'and', 'porque',
    ];

    private const EXCERPT_MAX_CHARS = 720;

    /**
     * @return list<array{title: string, path: string, content: string}>
     */
    public function listApprovedDocuments(): array
    {
        $documents = [];

        foreach ($this->collectApprovedMarkdownFiles() as $absolutePath) {
            $relativePath = $this->toRelativeDocPath($absolutePath);
            $content = (string) file_get_contents($absolutePath);

            $documents[] = [
                'title' => $this->resolveDocumentTitle($content, $relativePath),
                'path' => $relativePath,
                'content' => $content,
            ];
        }

        usort(
            $documents,
            static fn (array $left, array $right): int => strcmp($left['path'], $right['path']),
        );

        return $documents;
    }

    /**
     * @return list<array{title: string, path: string, excerpt: string, score: int}>
     */
    public function searchRelevantDocuments(string $query, int $limit = 3): array
    {
        $terms = $this->tokenizeQuery($query);

        if ($terms === []) {
            return [];
        }

        $conversionDirection = $this->resolveConversionDirection($query, $terms);
        $isArticuloDisponibilidadCargaQuery = $this->isArticuloDisponibilidadCargaQuery($query, $terms);
        $matches = [];

        foreach ($this->listApprovedDocuments() as $document) {
            $score = $this->scoreDocument($document, $terms, $conversionDirection, $isArticuloDisponibilidadCargaQuery);

            if ($score <= 0) {
                continue;
            }

            $matches[] = [
                'title' => $document['title'],
                'path' => $document['path'],
                'excerpt' => $this->buildExcerpt(
                    $document['content'],
                    $terms,
                    $conversionDirection,
                    $isArticuloDisponibilidadCargaQuery,
                ),
                'score' => $score,
            ];
        }

        usort(
            $matches,
            static fn (array $left, array $right): int => $right['score'] <=> $left['score'],
        );

        return array_slice($matches, 0, max(1, $limit));
    }

    /**
     * @return list<string>
     */
    private function collectApprovedMarkdownFiles(): array
    {
        $files = [];

        foreach (self::APPROVED_ROOTS as $root) {
            $absoluteDirectory = $this->docsRootPath().'/'.$root;

            if (! is_dir($absoluteDirectory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($absoluteDirectory, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $fileInfo) {
                if (! $fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'md') {
                    continue;
                }

                $relativePath = $this->toRelativeDocPath($fileInfo->getPathname());

                if ($this->isApprovedPath($relativePath)) {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }

        return $files;
    }

    private function isApprovedPath(string $relativePath): bool
    {
        foreach (self::EXCLUDED_PATH_FRAGMENTS as $fragment) {
            if (str_contains($relativePath, $fragment)) {
                return false;
            }
        }

        foreach (self::APPROVED_ROOTS as $root) {
            if (str_starts_with($relativePath, $root.'/') || $relativePath === $root) {
                return true;
            }
        }

        return false;
    }

    private function toRelativeDocPath(string $absolutePath): string
    {
        $docsRoot = $this->docsRootPath();
        $normalized = str_replace('\\', '/', $absolutePath);

        return ltrim(str_replace($docsRoot.'/', '', $normalized), '/');
    }

    private function docsRootPath(): string
    {
        $candidates = [
            base_path('../'.self::DOCS_FOLDER_NAME),
            base_path(self::DOCS_FOLDER_NAME),
        ];

        foreach ($candidates as $candidate) {
            $resolved = realpath($candidate);

            if ($resolved !== false && is_dir($resolved)) {
                return str_replace('\\', '/', $resolved);
            }
        }

        return str_replace('\\', '/', base_path('../'.self::DOCS_FOLDER_NAME));
    }

    private function resolveDocumentTitle(string $content, string $relativePath): string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches) === 1) {
            return trim($matches[1]);
        }

        $fileName = basename($relativePath, '.md');

        return str_replace(['-', '_'], ' ', $fileName);
    }

    /**
     * @return list<string>
     */
    private function tokenizeQuery(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $terms = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) < 3 || in_array($part, self::STOPWORDS, true)) {
                continue;
            }

            $terms[] = $part;
        }

        return array_values(array_unique($terms));
    }

    /**
     * @param  array{title: string, path: string, content: string}  $document
     * @param  list<string>  $terms
     */
    private function scoreDocument(array $document, array $terms, ?string $conversionDirection, bool $isArticuloDisponibilidadCargaQuery): int
    {
        $haystack = mb_strtolower($document['title'].' '.$document['content']);
        $score = 0;

        foreach ($terms as $term) {
            $score += substr_count($haystack, $term) * 2;
        }

        if (preg_match_all('/^#{2,3}\s+(.+)$/mu', $document['content'], $headingMatches) > 0) {
            foreach ($headingMatches[1] as $heading) {
                $lowerHeading = mb_strtolower($heading);
                $headingHits = 0;

                foreach ($terms as $term) {
                    if ($this->termMatchesText($term, $lowerHeading)) {
                        $headingHits++;
                    }
                }

                if ($headingHits >= 2) {
                    $score += 80;
                } elseif ($headingHits === 1) {
                    $score += 35;
                }
            }
        }

        if (str_ends_with($document['path'], '99-manual-usuario/PedidosWeb.md')) {
            foreach ($terms as $term) {
                if (in_array($term, ['leyendas', 'leyenda', 'presupuesto', 'presupuestos', 'pedido', 'pedidos', 'comprobante', 'grabar', 'carga'], true)) {
                    $score += 25;
                }
            }

            if ($conversionDirection === 'presupuesto_to_pedido') {
                $score += 120;
            }

            if ($conversionDirection === 'pedido_to_presupuesto') {
                $score += 120;
            }

            if ($isArticuloDisponibilidadCargaQuery) {
                $score += 140;
            }
        }

        return $score;
    }

    /**
     * @param  list<string>  $terms
     */
    private function buildExcerpt(string $content, array $terms, ?string $conversionDirection, bool $isArticuloDisponibilidadCargaQuery): string
    {
        $sections = preg_split('/\n(?=#+\s)/u', $content) ?: [$content];
        $bestSection = $content;
        $bestScore = -1;

        foreach ($sections as $section) {
            $sectionScore = $this->scoreSection($section, $terms);

            if ($isArticuloDisponibilidadCargaQuery) {
                if ($this->isArticuloDisponibilidadCargaSection($section)) {
                    $sectionScore += 220;
                } elseif ($this->isPresupuestoToPedidoSection($section) || $this->isPedidoToPresupuestoSection($section)) {
                    $sectionScore -= 150;
                }
            }

            if ($conversionDirection === 'presupuesto_to_pedido') {
                if ($this->isPresupuestoToPedidoSection($section)) {
                    $sectionScore += 200;
                } elseif ($this->isPedidoToPresupuestoSection($section)) {
                    $sectionScore -= 120;
                }
            }

            if ($conversionDirection === 'pedido_to_presupuesto') {
                if ($this->isPedidoToPresupuestoSection($section)) {
                    $sectionScore += 200;
                } elseif ($this->isPresupuestoToPedidoSection($section)) {
                    $sectionScore -= 120;
                }
            }

            if ($sectionScore > $bestScore) {
                $bestScore = $sectionScore;
                $bestSection = $section;
            }
        }

        $plainText = trim(preg_replace('/\s+/u', ' ', $this->stripMarkdownInline($bestSection)) ?? '');

        if ($plainText === '') {
            return '';
        }

        return trim(mb_substr($plainText, 0, self::EXCERPT_MAX_CHARS));
    }

    /**
     * @param  list<string>  $terms
     */
    private function scoreSection(string $section, array $terms): int
    {
        $lowerSection = mb_strtolower($section);
        $score = 0;

        foreach ($terms as $term) {
            $score += substr_count($lowerSection, $term) * 3;
        }

        if (preg_match('/^#+\s+(.+)$/mu', $section, $headingMatch) === 1) {
            $lowerHeading = mb_strtolower($headingMatch[1]);

            foreach ($terms as $term) {
                if ($this->termMatchesText($term, $lowerHeading)) {
                    $score += 40;
                }
            }
        }

        return $score;
    }

    /**
     * @param  list<string>  $terms
     */
    private function resolveConversionDirection(string $query, array $terms): ?string
    {
        $normalized = mb_strtolower(trim($query));

        if (! str_contains($normalized, 'presupuesto') || ! str_contains($normalized, 'pedido')) {
            return null;
        }

        $hasConversionIntent = in_array('pasar', $terms, true)
            || in_array('convertir', $terms, true)
            || in_array('conversion', $terms, true)
            || str_contains($normalized, 'estado');

        if (! $hasConversionIntent) {
            return null;
        }

        $presupuestoPos = mb_strpos($normalized, 'presupuesto');
        $pedidoPos = mb_strpos($normalized, 'pedido');

        if ($presupuestoPos === false || $pedidoPos === false) {
            return null;
        }

        return $presupuestoPos < $pedidoPos ? 'presupuesto_to_pedido' : 'pedido_to_presupuesto';
    }

    /**
     * @param  list<string>  $terms
     */
    private function isArticuloDisponibilidadCargaQuery(string $query, array $terms): bool
    {
        $normalized = mb_strtolower(trim($query));

        if (! str_contains($normalized, 'articul')) {
            return false;
        }

        $hasCargaContext = str_contains($normalized, 'carga')
            || (in_array('lista', $terms, true) && str_contains($normalized, 'pedido'));

        if (! $hasCargaContext) {
            return false;
        }

        return in_array('numeros', $terms, true)
            || in_array('numero', $terms, true)
            || str_contains($normalized, 'disponib')
            || str_contains($normalized, 'disp.')
            || str_contains($normalized, 'stock');
    }

    private function isArticuloDisponibilidadCargaSection(string $section): bool
    {
        $lowerSection = mb_strtolower($section);

        return str_contains($lowerSection, 'búsqueda de artículos')
            || str_contains($lowerSection, 'busqueda de articulos')
            || str_contains($lowerSection, 'significado de los números')
            || str_contains($lowerSection, 'significado de los numeros')
            || str_contains($lowerSection, 'dos números en la lista de artículos')
            || str_contains($lowerSection, 'dos numeros en la lista de articulos')
            || str_contains($lowerSection, 'disponible neto del artículo')
            || str_contains($lowerSection, 'disponible neto del articulo');
    }

    private function isPresupuestoToPedidoSection(string $section): bool
    {
        $lowerSection = mb_strtolower($section);

        return str_contains($lowerSection, 'presupuestos ingresados')
            || str_contains($lowerSection, 'paso un presupuesto a pedido')
            || str_contains($lowerSection, 'convertir presupuesto a pedido');
    }

    private function isPedidoToPresupuestoSection(string $section): bool
    {
        $lowerSection = mb_strtolower($section);

        return str_contains($lowerSection, 'pedidos ingresados')
            || str_contains($lowerSection, 'paso un pedido a presupuesto')
            || str_contains($lowerSection, 'pasar un pedido a presupuesto')
            || str_contains($lowerSection, 'convertir pedido a presupuesto');
    }

    private function termMatchesText(string $term, string $text): bool
    {
        if (str_contains($text, $term)) {
            return true;
        }

        if (mb_strlen($term) > 4 && str_ends_with($term, 's')) {
            return str_contains($text, mb_substr($term, 0, -1));
        }

        return false;
    }

    private function stripMarkdownInline(string $content): string
    {
        $withoutCode = preg_replace('/```.*?```/su', ' ', $content) ?? $content;
        $withoutLinks = preg_replace('/\[([^\]]+)\]\([^)]+\)/u', '$1', $withoutCode) ?? $withoutCode;
        $withoutEmphasis = preg_replace('/[*_`>#|-]/u', ' ', $withoutLinks) ?? $withoutLinks;

        return $withoutEmphasis;
    }
}
