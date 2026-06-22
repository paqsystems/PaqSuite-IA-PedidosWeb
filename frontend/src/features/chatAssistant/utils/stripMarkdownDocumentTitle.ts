export function stripMarkdownDocumentTitle(markdown: string): string {
  const lines = markdown.replace(/^\uFEFF/, '').split(/\r?\n/);
  const firstContentIndex = lines.findIndex((line) => line.trim() !== '');

  if (firstContentIndex === -1) {
    return '';
  }

  if (/^#\s+/.test(lines[firstContentIndex].trim())) {
    return lines
      .slice(firstContentIndex + 1)
      .join('\n')
      .replace(/^\s+/, '')
      .trimEnd();
  }

  return markdown.trim();
}
