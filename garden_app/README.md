# Idle Land Community Garden — Flutter Mobile & Web App

A modern, high-performance cross-platform Flutter application for the **Idle Land for Community Gardening System**, replacing the legacy Capacitor container with a native **Material Design 3 (Material You)** UI and REST API integration with the PHP/MySQL backend.

---

## 🌟 Key Features

### 1. Multi-Role Experience
* **Gardener**: Browse and filter idle land lots, apply for vacant plots with lease duration and purpose, view gardening task timelines (irrigation, planting, weeding), and log crop harvest yields (kg).
* **Landowner**: Manage registered parcels, inspect subdivided plot statuses (available vs occupied), and review incoming lease applications with one-tap Approve or Reject and response notes.
* **Administrator**: Platform overview, moderation of listed lots, user directory management, and status toggling (active/inactive).

### 2. Modern Material 3 UI / UX
* **Canvas & Surface Contrast**: Soft gray-blue canvas (`#F0F4F9`) with elevated white rounded containers (`20px - 24px` corner radii).
* **Google Fonts**: Clean typography using **Outfit** for headings and **Inter** for readable body and data labels.
* **Interactive Plots Grid**: Real-time visualization of lot subdivisions with availability badges, permitted crop tags, and direct application triggers.
* **One-Tap Demo Switcher**: Instant switching between Gardener, Landowner, and Admin roles directly from the Profile tab or Login screen for quick review and testing.

### 3. Native REST Backend Integration
* Direct communication with the PHP backend at `http://localhost/garden/api/` (or `http://10.0.2.2/garden/api/` on Android emulator / custom LAN IP).
* Built-in **Dynamic Server Configuration Dialog** with one-click presets and connection test verification.
* Resilient offline fallback dataset ensures uninterrupted functionality during offline demonstrations.

---

## 📁 Project Architecture

```
garden_app/
├── lib/
│   ├── constants/            # Application constants
│   ├── models/
│   │   └── models.dart       # User, Land, Plot, RequestModel, ScheduleItem, HarvestItem
│   ├── services/
│   │   ├── api_service.dart  # REST client with HTTP calls, timeout handling & fallbacks
│   │   └── auth_state.dart   # Session manager with SharedPreferences & role switching
│   ├── theme/
│   │   └── app_theme.dart    # Material 3 tokens, colors (#F0F4F9, #0B57D0, #15803D), typography
│   ├── widgets/
│   │   ├── app_header.dart
│   │   ├── stat_card.dart    # Metric presentation card
│   │   ├── status_badge.dart # Status pills (Approved, Pending, Occupied, etc.)
│   │   └── server_config_dialog.dart # In-app API endpoint switcher
│   ├── screens/
│   │   ├── auth/             # Login & Registration screens
│   │   ├── lands/            # Land listing, search/filter, property details, and listing dialog
│   │   ├── requests/         # Plot application and approval workflow
│   │   ├── schedules/        # Task timeline (watering, planting, weeding, etc.)
│   │   ├── harvests/         # Crop yield logging and metrics
│   │   ├── admin/            # User oversight and account toggles
│   │   ├── profile/          # User profile and demo role switcher
│   │   └── main_shell_screen.dart # Role-adaptive navigation bar
│   └── main.dart             # Application entry point
├── assets/
│   └── images/
│       └── logo.jpeg         # Application branding logo
└── pubspec.yaml              # Flutter dependencies (http, google_fonts, shared_preferences, intl)
```

---

## 🚀 Running the App

### Prerequisites
- Flutter SDK 3.38+ installed
- XAMPP Apache & MySQL running (with `idle_land_garden` database)

### Run on Windows / Desktop
```bash
cd garden_app
flutter run -d windows
```

### Run on Web (Chrome / Edge)
```bash
cd garden_app
flutter run -d chrome
```

### Run on Android
```bash
cd garden_app
flutter run -d android
```
*(Note: In the app's Server Settings dialog, select the `10.0.2.2` preset to connect to localhost from the Android emulator).*

---

## 🔑 Demo Credentials

| Role | Email | Password |
|---|---|---|
| **Gardener** | `gardener@garden.com` | `password123` |
| **Landowner** | `landowner@garden.com` | `password123` |
| **Admin** | `admin@garden.com` | `password123` |

*Quick chips are available on the Sign In screen to auto-fill and log in with a single tap.*
