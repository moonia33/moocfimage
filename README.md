# moocfimage

Cloudflare Image Optimization modulis PrestaShop parduotuvėms.

[![Download moocfimage.zip](https://img.shields.io/badge/download-moocfimage.zip-blue)](https://github.com/moonia33/moocfimage/releases/latest/download/moocfimage.zip)

## Funkcionalumas

- Perrašo produktų paveikslėlių URL į Cloudflare `/cdn-cgi/image` formą.
- Naudoja `fit=scale-down` ir `quality` (numatyta 85), plotis pagal PrestaShop `ImageType`.
- Galima įjungti/išjungti tiesiog moduliu (be kodo keitimo).

## Reikalavimai

- PrestaShop 9.x arba naujesnė versija.
- Cloudflare Image Resizing įjungtas jūsų zone.

## Diegimas iš ZIP (Back-office)

1. Atsisiųskite ZIP iš [Latest Release](https://github.com/moonia33/moocfimage/releases/latest).
2. BO → Modules → Module Manager → Upload a module → pasirinkite ZIP.

## Automatinis ZIP per GitHub Releases

Šis repo turi GitHub Actions workflow, kuris ant tag’o `v*` sukuria ZIP ir prideda prie Release:

```bash
# vienkartinis: nustatykite vardą/el. paštą šiame repo
git config user.name "Livia Corsetti"
git config user.email "tavo-github-email@example.com"

# pirmas push
git add .
git commit -m "chore: initial"
git branch -M main
git remote add origin https://github.com/moonia33/moocfimage.git
git push -u origin main

# išleidimas (sukuria ZIP per Actions)
git tag v1.0.0
git push origin v1.0.0
```

Workflow zip’ina modulį kaip `moocfimage.zip` ir versinį `moocfimage-<version>.zip` (žr. `.github/workflows/release.yml`).

## Konfigūracija

- `MOOCFIMAGE_ENABLED` (1/0) – ar transformuoti paveikslėlių URL.
- `MOOCFIMAGE_QUALITY` (1–100) – Cloudflare `quality` parametras, numatyta 85.

Daugiau apie Cloudflare Image Resizing: https://developers.cloudflare.com/images/transform-images/transform-via-url/
