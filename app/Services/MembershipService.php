<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Service pour gérer l'adhésion aux collocations.
 *
 * Règles métier:
 * - Un utilisateur ne peut avoir qu'UNE seule colocation ACTIVE à la fois
 * - Une colocation est ACTIVE si:
 *   - membership.left_at = NULL (membre actif)
 *   - collocation.status = 'active' (colocation active)
 */
class MembershipService
{
    /**
     * Vérifie si un utilisateur a déjà une colocation active.
     *
     * @param User $user
     * @return bool
     */
    public function userHasActiveCollocation(User $user): bool
    {
        return $user->collocations()
            ->wherePivotNull('left_at')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Récupère la colocation active de l'utilisateur (s'il y en a une).
     *
     * @param User $user
     * @return Collocation|null
     */
    public function getUserActiveCollocation(User $user): ?Collocation
    {
        return $user->collocations()
            ->wherePivotNull('left_at')
            ->where('status', 'active')
            ->first();
    }

    /**
     * Valide que l'utilisateur peut créer/rejoindre une nouvelle colocation.
     *
     * Lance une ValidationException si l'utilisateur a déjà une colocation active.
     *
     * @param User $user
     * @param string $context
     * @throws ValidationException
     * @return void
     */
    public function validateCanJoinCollocation(User $user, string $context = 'join'): void
    {
        if ($this->userHasActiveCollocation($user)) {
            $activeCollocation = $this->getUserActiveCollocation($user);

            throw ValidationException::withMessages([
                'collocation' => "Vous avez déjà une colocation active: '{$activeCollocation->name}'. "
                    . "Quittez-la ou terminez-la avant d'en rejoindre une autre.",
            ]);
        }
    }

    /**
     * Valide que l'utilisateur peut créer une nouvelle colocation.
     *
     * @param User $user
     * @throws ValidationException
     * @return void
     */
    public function validateCanCreateCollocation(User $user): void
    {
        $this->validateCanJoinCollocation($user, 'create');
    }

    /**
     * Attache un utilisateur à une colocation.
     *
     * Effectue les vérifications et attachements corrects.
     *
     * @param User $user
     * @param Collocation $collocation
     * @param array $pivotData
     * @return void
     */
    public function attachUserToCollocation(User $user, Collocation $collocation, array $pivotData = []): void
    {
        // Validation préalable
        $this->validateCanJoinCollocation($user);

        // Données par défaut
        $defaultPivotData = [
            'role' => 'member',
            'joined_at' => now(),
        ];

        $finalPivotData = array_merge($defaultPivotData, $pivotData);

        // Attachement
        if (!$collocation->members()->where('user_id', $user->id)->exists()) {
            $collocation->members()->attach($user->id, $finalPivotData);
        }
    }

    /**
     * Obtient toutes les collocations d'un utilisateur (actives et inactives).
     *
     * @param User $user
     * @return mixed
     */
    public function getUserAllCollocations(User $user)
    {
        return $user->collocations()
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Obtient les collocations actives d'un utilisateur.
     *
     * @param User $user
     * @return mixed
     */
    public function getUserActiveCollocations(User $user)
    {
        return $user->collocations()
            ->wherePivotNull('left_at')
            ->where('status', 'active')
            ->get();
    }
}
