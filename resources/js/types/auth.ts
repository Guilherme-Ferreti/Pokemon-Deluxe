export type User = {
  id: number;
  name: string;
  email: string;
  avatar?: string;
};

export type Auth = {
  user: User;
};
