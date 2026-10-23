<?php

declare(strict_types=1);

namespace Buffalo\Security;

use Buffalo\Domain\Usuario;
use Buffalo\Repository\UsuarioRepository;

/**
 * Checks a credential, separately from looking the user up.
 *
 * The legacy query matched on usuario AND passwd in one interpolated string,
 * so the lookup and the check were the same step -- there was nowhere to put
 * hashing, and the password was compared by the database in clear text.
 */
final readonly class Authenticator
{
    /**
     * A real bcrypt hash of a value nobody knows, verified against when no
     * user matches so that a wrong name and a wrong password take about the
     * same time.
     */
    private const string DUMMY_HASH = '$2y$12$C6UzMDM.H6dfI/f/IKcEe.NG2PHRXJLzRlzGxLzKkHhCQxTnFDh6a';

    public function __construct(private UsuarioRepository $usuarios)
    {
    }

    public function attempt(string $usuario, string $password): ?Usuario
    {
        $found = $this->usuarios->findByUsername($usuario);

        if ($found === null) {
            password_verify($password, self::DUMMY_HASH);

            return null;
        }

        if (self::isHashed($found->storedPassword)) {
            return password_verify($password, $found->storedPassword) ? $found : null;
        }

        // A row still holding clear text, which means an old dump was
        // restored over the migration. Accept it once on a correct password,
        // then upgrade it so this branch stops being reachable for that user.
        if (!hash_equals($found->storedPassword, $password)) {
            return null;
        }

        $this->usuarios->updatePassword($found->id, self::hash($password));

        return $found;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function isHashed(string $stored): bool
    {
        return password_get_info($stored)['algo'] !== null;
    }
}
