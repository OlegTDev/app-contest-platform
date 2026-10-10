import { PageProps, User } from "@/types";
import { usePage } from "@inertiajs/react";

interface UserAuthReturn {
  user: User | null;
  role: string;
  hasRole: (role: string) => boolean;
  isAdmin: boolean;
  isModerator: boolean;
  canManageContests: boolean;
}

export function useAuth(): UserAuthReturn {
  const { auth } = usePage<PageProps>().props;

  const user = auth?.user ?? null;
  const role = auth?.user?.role ?? '';

  const hasRole = (roleParam: string): boolean => {
    if (!user) return false;
    return roleParam == role;
  };

  return {
    user,
    role,
    hasRole,
    isAdmin: hasRole('admin'),
    isModerator: hasRole('moderator'),
    // Mirrors the 'moderator' middleware on the contest-management route group:
    // contest CRUD belongs to moderators only, admins keep user management.
    canManageContests: hasRole('moderator'),
  };
}
