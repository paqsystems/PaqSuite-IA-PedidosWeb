import { describe, expect, it } from 'vitest';
import { loadInitialMessage } from '../content/loadInitialMessage';
import { loadSupportFollowupMessage } from '../content/loadSupportFollowupMessage';
import { replaceMessagePlaceholders } from './replaceMessagePlaceholders';
import { renderSafeMarkdown } from './renderSafeMarkdown';
import { stripMarkdownDocumentTitle } from './stripMarkdownDocumentTitle';

describe('stripMarkdownDocumentTitle', () => {
  it('removes the leading markdown title', () => {
    const input = '# Titulo\n\nParrafo uno.\n\nParrafo dos.';
    expect(stripMarkdownDocumentTitle(input)).toBe('Parrafo uno.\n\nParrafo dos.');
  });
});

describe('replaceMessagePlaceholders', () => {
  it('replaces proyecto and support email placeholders', () => {
    const input = 'Hola {{Proyecto}}. Contacto: `{{supportEmail}}`.';
    const output = replaceMessagePlaceholders(input, {
      proyecto: 'Pedidos Web',
      supportEmail: 'ayuda@paqsystems.com.ar',
    });

    expect(output).toBe('Hola Pedidos Web. Contacto: `ayuda@paqsystems.com.ar`.');
    expect(output).not.toContain('{{');
  });
});

describe('renderSafeMarkdown', () => {
  it('renders paragraphs and inline code without raw html', () => {
    const html = renderSafeMarkdown('Linea uno.\n\nContacto `ayuda@test.com`.\n\n<script>alert(1)</script>');
    expect(html).toContain('<p>Linea uno.</p>');
    expect(html).toContain('<code>ayuda@test.com</code>');
    expect(html).not.toContain('<script>');
  });
});

describe('loadInitialMessage', () => {
  it('loads markdown content with proyecto placeholder resolved', () => {
    const message = loadInitialMessage();
    expect(message).toContain('asistente de ayuda de');
    expect(message).not.toContain('{{Proyecto}}');
  });
});

describe('loadSupportFollowupMessage', () => {
  it('loads markdown content with support email placeholder resolved', () => {
    const message = loadSupportFollowupMessage();
    expect(message).toContain('ayuda@paqsystems.com.ar');
    expect(message).not.toContain('{{supportEmail}}');
  });
});
