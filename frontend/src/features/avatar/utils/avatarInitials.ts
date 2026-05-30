export function resolveAvatarInitials(displayName: string): string {
  const trimmed = displayName.trim();

  if (trimmed === '') {
    return '?';
  }

  const words = trimmed.split(/\s+/).filter(Boolean);

  if (words.length >= 2) {
    return `${words[0]![0] ?? ''}${words[1]![0] ?? ''}`.toUpperCase();
  }

  return trimmed.slice(0, 2).toUpperCase();
}
