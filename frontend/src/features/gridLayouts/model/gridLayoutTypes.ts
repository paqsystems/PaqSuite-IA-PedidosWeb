export type GridLayoutListItem = {
  id: number;
  layoutName: string;
  createdByUserId: number;
  isOwner: boolean;
  updatedAt?: string | null;
};

export type GridLayoutActive = {
  layoutId: number | null;
  layoutName: string | null;
  stateJson: Record<string, unknown> | null;
};

export type GridLayoutSelectItem = {
  id: number | null;
  layoutName: string;
};
