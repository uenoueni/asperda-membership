---
name: Asperda Institutional System
colors:
  surface: '#f9f9ff'
  surface-dim: '#d3daea'
  surface-bright: '#f9f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f3ff'
  surface-container: '#e7eefe'
  surface-container-high: '#e2e8f8'
  surface-container-highest: '#dce2f3'
  on-surface: '#151c27'
  on-surface-variant: '#43474e'
  inverse-surface: '#2a313d'
  inverse-on-surface: '#ebf1ff'
  outline: '#74777f'
  outline-variant: '#c4c6cf'
  surface-tint: '#455f87'
  primary: '#022448'
  on-primary: '#ffffff'
  primary-container: '#1e3a5f'
  on-primary-container: '#8aa4cf'
  inverse-primary: '#adc8f5'
  secondary: '#0060ac'
  on-secondary: '#ffffff'
  secondary-container: '#68abff'
  on-secondary-container: '#003e73'
  tertiary: '#202427'
  on-tertiary: '#ffffff'
  tertiary-container: '#363a3c'
  on-tertiary-container: '#a0a4a6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d5e3ff'
  primary-fixed-dim: '#adc8f5'
  on-primary-fixed: '#001c3b'
  on-primary-fixed-variant: '#2d486d'
  secondary-fixed: '#d4e3ff'
  secondary-fixed-dim: '#a4c9ff'
  on-secondary-fixed: '#001c39'
  on-secondary-fixed-variant: '#004883'
  tertiary-fixed: '#e0e3e6'
  tertiary-fixed-dim: '#c3c7ca'
  on-tertiary-fixed: '#181c1e'
  on-tertiary-fixed-variant: '#43474a'
  background: '#f9f9ff'
  on-background: '#151c27'
  surface-variant: '#dce2f3'
typography:
  headline-xl:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Manrope
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  container-max-width: 800px
  section-gap: 2.5rem
  stack-gap: 1.25rem
  inline-gap: 1rem
  form-padding: 2rem
  margin-mobile: 1rem
  margin-desktop: auto
---

## Brand & Style

The design system is engineered for institutional trust and professional efficiency. It caters to business owners and administrative professionals within the ASPERDA ecosystem. The aesthetic is **Corporate / Modern**, leaning heavily into high-clarity typography and a structured information hierarchy.

The UI avoids decorative flourishes in favor of utility and precision. It utilizes a restrained color palette, subtle depth through layering, and a clear "progressive disclosure" model via a multi-step wizard to prevent cognitive overload during complex data entry. The goal is to evoke a sense of reliability, officiality, and ease of use.

## Colors

The palette is anchored by "Deep Institutional Blue," providing a sense of stability and authority.

- **Primary (#1E3A5F):** Used for headers, primary actions, and brand-critical elements.
- **Secondary (#4A90E2):** Used for focus states, progress indicators, and interactive links to provide a modern, accessible accent.
- **Surface/Tertiary (#F4F7FA):** A cool-toned off-white used for background fills to reduce eye strain compared to pure white.
- **Neutral (#6B7280):** Specifically tuned for legible secondary text and field labels.
- **Semantic Colors:** Success (Emerald), Error (Crimson), and Warning (Amber) should be used sparingly for form validation feedback.

## Typography

The system utilizes a dual-font strategy. **Manrope** is used for headlines to provide a modern, geometric, yet professional character. **Inter** is used for all functional UI elements, body text, and labels due to its exceptional legibility in data-heavy forms.

- **Contrast:** High contrast between labels (uppercase, bold) and input text (regular) ensures users can quickly scan the form structure.
- **Spacing:** Generous line-heights are maintained to ensure readability in multi-line descriptions and instructions.

## Layout & Spacing

This design system employs a **Fixed Grid** approach for the registration wizard, centering the content to keep the user focused on the task at hand.

- **The Wizard Container:** Max-width of 800px on desktop to prevent long line lengths and keep form fields within a comfortable peripheral range.
- **Vertical Rhythm:** A consistent 8px-based spacing scale is used. Sections are separated by `section-gap`, while individual form fields are grouped using `stack-gap`.
- **Responsive Behavior:**
    - **Desktop:** Centered fixed-width card with ample white space.
    - **Tablet:** 12-column fluid grid with 40px side margins.
    - **Mobile:** Single-column fluid layout with 16px margins; headers become sticky to maintain context during long scrolls.

## Elevation & Depth

Hierarchy is established through **Tonal Layering** and **Ambient Shadows**.

- **Base Layer:** The application background uses the Tertiary color (#F4F7FA).
- **Surface Layer:** The registration form sits on a pure white card with a subtle, large-radius shadow (15% opacity, 20px blur) to create a distinct floating effect without appearing heavy.
- **Interactive Depth:** Buttons use a slight "lift" on hover, achieved by increasing shadow density. Inactive fields remain flat with a light neutral border.
- **Section Dividers:** Horizontal rules are used not just to separate content, but to visually organize "Information Blocks" within the card.

## Shapes

The shape language is consistently **Rounded**, striking a balance between modern friendliness and corporate structure.

- **Primary Components:** Form inputs and primary buttons utilize a 0.5rem (8px) radius.
- **Container Elements:** The main wizard card uses a 1rem (16px) radius for a softer, more inviting appearance.
- **Sub-components:** Progress bars and tags utilize a fully rounded (pill) style to distinguish them from actionable form inputs.

## Components

### Buttons
- **Primary:** Solid #1E3A5F background, white text. Large padding (12px 24px). Full-width for mobile, auto-width for desktop "Next" actions.
- **Secondary/Back:** Outlined with a 1px #D1D5DB border and #1E3A5F text.

### Form Fields
- **Input Fields:** 1px border (#D1D5DB). On focus, the border transitions to Primary Blue with a 3px soft outer glow (Secondary Blue at 20% opacity).
- **Labels:** Positioned above the field in `label-md` style, using Neutral text to distinguish from user input.
- **Error States:** Field border changes to Red-600 with a supporting error message in `label-sm` below the field.

### Progress Wizard
- **Stepper:** A horizontal indicator at the top of the card. Completed steps show a check icon; the active step is highlighted with a Secondary Blue circle.
- **Section Headers:** Bold headings with a thin bottom border to group related inputs (e.g., "Bank Information").

### File Upload
- **Dropzone:** A dashed border area with a light grey background. Includes a "Browse" button and a clear list of accepted formats and size limits.

### Cards
- **Form Card:** The main wrapper for the wizard. Features a high-contrast header (Primary Blue background) where the ASPERDA logo and step title reside.