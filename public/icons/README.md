# PWA Icons

Generated from a 512×512 source via [realfavicongenerator.net](https://realfavicongenerator.net/).

## Files in this directory

- `web-app-manifest-192x192.png` — 192×192 PWA icon, `purpose: maskable` (designed with inner 80% safe zone)
- `web-app-manifest-512x512.png` — 512×512 PWA icon, `purpose: maskable`
- `apple-touch-icon.png` — 180×180, iOS home-screen icon (referenced from `<head>`)
- `favicon.svg`, `favicon.ico`, `favicon-96x96.png` — browser favicons

If a non-maskable (`purpose: "any"`) variant is added later, add a separate
`icons` entry to `manifest.webmanifest` so launchers that don't mask have an
unpadded icon to fall back to.
