# Dashboard Assets

Folder ini berisi file CSS dan JavaScript untuk dashboard Order Service.

## Struktur File

```
assets/
├── dashboard.css       # Semua styling untuk dashboard
├── dashboard.js        # Semua JavaScript interaktif
└── README.md          # Dokumentasi ini
```

## File CSS (dashboard.css)

### Fitur Utama:
- **CSS Variables** - Menggunakan custom properties untuk tema yang mudah diubah
- **Dark Mode** - Support tema gelap dengan `body[data-theme="dark"]`
- **Responsive Design** - Breakpoints untuk mobile, tablet, dan desktop
- **Grid Layout** - Modern CSS Grid untuk layout yang fleksibel
- **Animations** - Smooth transitions dan loading animations

### Struktur CSS:
1. **Variables** - Warna, spacing, shadows
2. **Base Styles** - Reset dan typography
3. **Layout** - Grid, sidebar, main content
4. **Components** - Buttons, cards, forms, tables
5. **Utilities** - Helper classes
6. **Media Queries** - Responsive breakpoints

## File JavaScript (dashboard.js)

### Fitur Utama:
- **Theme Toggle** - Switch antara light/dark mode dengan localStorage
- **Tab Navigation** - Navigasi antar panel Users, Foods, Orders
- **Toast Notifications** - Auto-hide notifications setelah 4 detik
- **Loading Overlay** - Loading state untuk form submissions
- **Form Validation** - Client-side validation
- **Keyboard Shortcuts** - Ctrl+K (search), Ctrl+D (dark mode)

### Fungsi Utama:
- `formatRupiah(number)` - Format angka ke format Rupiah
- `showToast(message, type)` - Tampilkan toast notification

## Cara Menggunakan

### Di Blade Template:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
</head>
<body>
    <!-- Your HTML content -->
    
    <!-- JavaScript -->
    <script src="{{ asset('assets/dashboard.js') }}"></script>
</body>
</html>
```

## Customization

### Mengubah Warna Brand:

Edit variabel di `dashboard.css`:

```css
:root {
    --brand: #0f7c82;        /* Warna utama */
    --brand-dark: #0b5f63;   /* Warna gelap */
    --accent: #f59e0b;       /* Warna aksen */
}
```

### Menambah Breakpoint Baru:

```css
@media (max-width: 1440px) {
    /* Your responsive styles */
}
```

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Tips

1. **CSS** sudah di-minify untuk production
2. **JavaScript** menggunakan event delegation untuk performa optimal
3. **Animations** menggunakan CSS transforms untuk hardware acceleration
4. **Images** - Gunakan lazy loading untuk gambar besar

## Troubleshooting

### Dark mode tidak tersimpan?
- Pastikan localStorage tidak diblokir oleh browser
- Check console untuk error

### Tab navigation tidak bekerja?
- Pastikan setiap button memiliki `data-target` attribute
- Pastikan panel memiliki ID yang sesuai

### Loading overlay tidak muncul?
- Check apakah form memiliki attribute `data-no-loading`
- Pastikan JavaScript sudah di-load

## Changelog

### v1.0.0 (2026-04-25)
- Initial release
- Separated CSS and JS from Blade template
- Added keyboard shortcuts
- Improved form validation
- Added loading states
