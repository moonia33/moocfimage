# moocfimage

Cloudflare Image Optimization modulis PrestaShop parduotuvėms.

## Funkcionalumas

- Perrašo produktų paveikslėlių URL į Cloudflare `/cdn-cgi/image` formą.
- Naudoja `fit=scale-down` ir nurodytą `width` + `quality` pagal paveikslėlio tipą.
- Gali būti lengvai išjungtas per modulio išjungimą PrestaShop administracijoje.

## Reikalavimai

- PrestaShop 9.x arba naujesnė versija.
- Cloudflare Image Resizing įjungtas jūsų zone.

## Diegimas iš ZIP

1. Supakuokite modulio katalogą į ZIP:

```bash
cd modules
zip -r moocfimage.zip moocfimage
```

2. Prisijunkite prie PrestaShop administracijos.
3. Eikite į **Modules & Services → Module manager**.
4. Pasirinkite **Upload a module** ir įkelkite `moocfimage.zip`.

## Git workflow

```bash
cd modules/moocfimage
git init
git remote add origin git@github.com:moonia33/moocfimage.git
git add .
git commit -m "Initial commit"
# vėliau:
# git push -u origin main
```

## Konfigūracija

- `MOOCFIMAGE_ENABLED` (1/0) – ar transformuoti paveikslėlių URL.
- `MOOCFIMAGE_QUALITY` (1–100) – Cloudflare `quality` parametras, numatyta 85.

Daugiau apie Cloudflare Image Resizing: https://developers.cloudflare.com/images/transform-images/transform-via-url/
