# Design System

## 1. Syntax System & Architecture Design

- **Cascade Layers (`@layer`):**
- Explicit ordering to manage specificity: `reset, system, base, layout, components, utilities`.

- **Design Tokens Scope:**
- Centralized configuration declared globally within the `:root` pseudo-class under the `system` layer.

- **Modern Color Space:**
- Utilization of the `oklch()` functional notation for uniform perceptual color mapping.

- **Dynamic Specifications:**
- Implementations of runtime functional evaluations (`calc()`, `color-mix()`) and explicit boundary constructs (`calc(infinity * 1px)` for absolute border radiuses).

- **Advanced Attribute Selectors:**
- Complex targeting architectures leveraging state-driven pseudo-classes (`:is()`, `:has()`, `:focus-within`, `:focus-visible`) and explicitly bound data-attributes (`[data-state]`, `[data-variant]`, `[data-size]`).

---

## 2. Structural Style Groups & References

### A. Base Configurations (`base.css`)

- **Design Tokens Group:**
- _Reference:_ `@layer system -> :root`

- **Dark Mode Matrix:**
- _Reference:_ `@media (prefers-color-scheme: dark) -> :root`

### B. Interface Components (`common.css`)

All groups below are securely bounded within the `@layer components` partition:

- **Avatar Component:**
- _Reference:_ `.avatar`, `.avatar__image`, `.avatar__info`, `.avatar__name`, `.avatar__email`

- **Interactive Effect Helpers:**
- _Reference:_ `.image-wrapper:hover .image`, `.link-hover--standout`, `.link-hover--underline`, `.hover-lift`, `.bouncy-btn`

- **Loader / Spinner Component:**
- _Reference:_ `#preloader`, `#preloader .spinner`, `@keyframes spin`

- **Breadcrumb Component:**
- _Reference:_ `.breadcrumb__list`, `.breadcrumb__item`, `.breadcrumb__separator`, `.breadcrumb__link`, `.breadcrumb__page`

- **Separator Component:**
- _Reference:_ `.separator`, `.separator[data-orientation="vertical"]`

- **Button Component:**
- _Reference:_ `.btn`, `.btn[data-size]`, `.btn[data-variant]`

- **Form Controls / Field Component:**
- _Reference:_ `.switch`, `.radio-group`, `.field`, `.field__label`, `.field__input`, `.password-field`, `textarea.field__input`, `input[type="checkbox"]`, `.field__error`

- **Alert & Toast Notifications Component:**
- _Reference:_ `.alert`, `.toast`, `#toast-portal`, `.alert[data-variant]`, `.toast[data-variant]`, `@keyframes alertFadeIn`

- **Collapsible Component:**
- _Reference:_ `.collapsible__content`, `.collapsible__content[data-state]`

- **Popover Component:**
- _Reference:_ `.popover`, `.popover__content`, `.popover__header`, `.popover__title`, `.popover__description`

- **Modal Component:**
- _Reference:_ `.modal`, `.modal__header`, `.modal__footer`, `@keyframes enter`, `@keyframes exit`
