import { PageProps, User } from "@/types";
import { usePage } from "@inertiajs/react";

interface UserAuthReturn {
  user: User | null;
  role: string;
  hasRole: (role: string) => boolean;
  isAdmin: boolean;
}

export function useAuth(): UserAuthReturn {
  const { auth } = usePage<PageProps>().props;

  const user = auth?.user;
  const role = auth?.user?.role;

  const hasRole = (roleParam: string): boolean => {
    if (!user) return false;
    return roleParam == role;
  };

  return {
    user,
    role,
    hasRole,
    isAdmin: hasRole('admin'),
  };
}
