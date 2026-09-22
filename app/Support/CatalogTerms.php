<?php

namespace App\Support;

use App\Enums\Gender;
use App\Models\Cut;
use App\Models\Wash;
use Illuminate\Support\Collection;

/**
 * Le vocabulaire du catalogue — coupes et lavages — chargé une seule fois
 * par requête et indexé par identifiant court.
 *
 * Les produits ne stockent que cet identifiant ; tout le reste de
 * l'application passe par ici pour obtenir le libellé ou le code SKU, sans
 * relation à charger et sans risque de requête en cascade.
 */
final class CatalogTerms
{
    /** @var Collection<string, Cut>|null */
    private ?Collection $cuts = null;

    /** @var Collection<string, Wash>|null */
    private ?Collection $washes = null;

    /**
     * @return Collection<string, Cut>
     */
    public function cuts(): Collection
    {
        return $this->cuts ??= Cut::query()->ordered()->get()->keyBy('slug');
    }

    /**
     * @return Collection<string, Wash>
     */
    public function washes(): Collection
    {
        return $this->washes ??= Wash::query()->ordered()->get()->keyBy('slug');
    }

    public function cut(?string $slug): ?Cut
    {
        return $slug === null ? null : $this->cuts()->get($slug);
    }

    /**
     * Retrouve une coupe depuis le segment d'adresse : « wide-leg » → wide_leg.
     */
    public function cutByUrlSegment(?string $segment): ?Cut
    {
        return $segment === null ? null : $this->cut(str_replace('-', '_', $segment));
    }

    public function wash(?string $slug): ?Wash
    {
        return $slug === null ? null : $this->washes()->get($slug);
    }

    /**
     * Le libellé, ou l'identifiant lui-même si la coupe a été retirée : une
     * fiche ancienne reste lisible plutôt que d'afficher un vide.
     */
    public function cutLabel(?string $slug): string
    {
        $coupe = $this->cut($slug);

        return $coupe === null ? (string) $slug : $coupe->name;
    }

    public function washLabel(?string $slug): string
    {
        $lavage = $this->wash($slug);

        return $lavage === null ? (string) $slug : $lavage->name;
    }

    /**
     * Les coupes encore proposées, éventuellement restreintes à un genre.
     *
     * @return Collection<int, Cut>
     */
    public function activeCuts(?Gender $gender = null): Collection
    {
        return $this->cuts()
            ->filter(fn (Cut $cut): bool => $cut->is_active)
            ->filter(fn (Cut $cut): bool => $gender === null || $cut->isAvailableFor($gender))
            ->values();
    }

    /**
     * @return Collection<int, Wash>
     */
    public function activeWashes(): Collection
    {
        return $this->washes()->filter(fn (Wash $wash): bool => $wash->is_active)->values();
    }

    /**
     * Appelé dès qu'une coupe ou un lavage change, pour que la même requête
     * ne travaille pas sur un vocabulaire périmé.
     */
    public function forget(): void
    {
        $this->cuts = null;
        $this->washes = null;
    }
}
