function escapeHtml(text: string): string {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function renderInlineMarkdown(text: string): string {
  return escapeHtml(text).replace(/`([^`]+)`/g, '<code>$1</code>');
}

export function renderSafeMarkdown(markdown: string): string {
  const normalized = markdown.trim();

  if (normalized === '') {
    return '';
  }

  return normalized
    .split(/\n\s*\n/)
    .map((paragraph) => {
      const inline = renderInlineMarkdown(paragraph.replace(/\n/g, ' ').trim());
      return `<p>${inline}</p>`;
    })
    .join('');
}
