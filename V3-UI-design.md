# V3 UI Design Guidelines - Apple/iOS Inspired

## Falsafah Design

Design sistem kita berasaskan **Apple Human Interface Guidelines (HIG)** dan **Liquid Glass Design Language** yang diperkenalkan Apple pada 2025. Fokus utama adalah:

- **Clarity (Kejelasan)**: Interface yang bersih, tepat, dan tidak berselerak
- **Consistency (Konsistensi)**: Elemen UI yang standard dan familiar
- **Deference (Penumpuan)**: Kandungan adalah raja, UI tidak ganggu
- **Depth (Kedalaman)**: Gunakan layers dan motion untuk hierarchy yang jelas

---

## Prinsip Utama Design

### 1. Kurangkan Icon AI-Generated

**JANGAN:**
- Guna icon yang nampak generic/AI-generated
- Terlalu banyak gradient kompleks
- Icon yang terlalu detail atau busy
- Inconsistent style antara icon

**GUNA:**
- SF Symbols (Apple's official icon library - 6,900+ icons)
- Simple geometric shapes
- Minimalist line icons
- Monochrome atau limited color palette (2-3 warna max)
- Consistent stroke weight across all icons

**Contoh Approach:**
```
❌ Icon dengan banyak gradient, shadow, 3D effect
✅ Simple line icon, 2pt stroke, monochrome
✅ SF Symbols dari Apple Design Resources
```

### 2. Liquid Glass Material

Liquid Glass adalah material translucent yang:
- Reflect dan refract persekitaran
- Adapt antara light dan dark mode
- Dynamic react kepada movement
- Beri focus kepada content

**Aplikasi:**
- Buttons, switches, sliders
- Tab bars dan sidebars
- Navigation elements
- Cards dan containers
- Modal sheets

**Properties:**
```css
/* Contoh Liquid Glass Effect */
background: rgba(255, 255, 255, 0.1);
backdrop-filter: blur(20px) saturate(180%);
border: 1px solid rgba(255, 255, 255, 0.2);
border-radius: 16px;
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
```

### 3. Typography

**Font System:**
- **Primary**: San Francisco (Apple's system font)
- **Default Size**: 17pt untuk body text
- **Hierarchy**: Guna weight (Regular, Medium, Semibold, Bold) bukan size sahaja

**Guidelines:**
```
Display/Hero: 34pt Bold
Title 1: 28pt Bold
Title 2: 22pt Bold
Title 3: 20pt Semibold
Headline: 17pt Semibold
Body: 17pt Regular
Callout: 16pt Regular
Subhead: 15pt Regular
Footnote: 13pt Regular
Caption: 12pt Regular
```

**Contrast:**
- Minimum contrast ratio: 4.5:1 untuk body text
- 3:1 untuk large text (18pt+)
- Test dengan Liquid Glass backgrounds

### 4. Color System

**Approach:**
- Guna semantic colors (systemBlue, systemGray, etc.)
- Limited palette: 2-3 primary colors
- Avoid arbitrary gradients
- Support light & dark mode automatically

**Semantic Colors:**
```
Primary Action: systemBlue (#007AFF)
Destructive: systemRed (#FF3B30)
Success: systemGreen (#34C759)
Warning: systemOrange (#FF9500)
Neutral: systemGray (#8E8E93)
```

**Dark Mode:**
- Automatically adapt colors
- Test semua UI dalam kedua-dua mode
- Guna elevated backgrounds untuk layers

### 5. Spacing & Layout

**Grid System:**
- Base unit: 8pt
- Padding: 16pt, 24pt, 32pt
- Margins: 16pt (mobile), 24pt (tablet)

**Touch Targets:**
- Minimum: 44x44pt untuk semua interactive elements
- Spacing between: minimum 8pt

**Corner Radius:**
- Small elements (buttons): 8-12pt
- Cards: 16pt
- Modals: 20pt
- Match dengan device rounded corners

### 6. Navigation Patterns

**Tab Bar (Bottom Navigation):**
- Maximum 5 items
- Icon + label (optional)
- Shrink on scroll, expand on scroll up
- Liquid Glass material

**Navigation Bar (Top):**
- Large title yang collapse on scroll
- Back button (chevron + label)
- Action buttons (max 2-3)
- Translucent background

**Sidebar (iPad/Desktop):**
- Refract content behind
- Reflect wallpaper
- Collapsible sections
- Clear hierarchy

### 7. Components

#### Buttons
```
Primary: Filled, Liquid Glass, rounded
Secondary: Outlined, transparent
Tertiary: Text only
Destructive: Red tint

States: Default, Pressed, Disabled
Animation: Subtle scale (0.95) on press
```

#### Cards
```
Background: Liquid Glass material
Padding: 16pt
Corner radius: 16pt
Shadow: Subtle, elevated
Hover: Slight lift effect
```

#### Forms
```
Input fields: Rounded (12pt), subtle border
Labels: Above input, 15pt Subhead
Placeholders: systemGray
Focus state: systemBlue border
Error state: systemRed border + message
```

#### Modals & Sheets
```
Background: Liquid Glass
Corner radius: 20pt (top corners)
Drag indicator: Centered, subtle
Backdrop: Dark overlay (0.4 opacity)
Animation: Smooth slide up/down
```

### 8. Animation & Motion

**Principles:**
- Subtle dan purposeful
- Duration: 200-300ms untuk most interactions
- Easing: ease-in-out (natural feel)
- Avoid jarring movements

**Common Animations:**
```
Button press: Scale 0.95, 100ms
Page transition: Slide, 300ms
Modal present: Slide up, 350ms
Loading: Subtle pulse atau spinner
Success: Checkmark with scale
```

### 9. Iconography Guidelines

**Style:**
- Line-based (bukan filled by default)
- 2pt stroke weight
- Rounded line caps
- 24x24pt atau 28x28pt size
- Optical alignment (bukan mathematical center)

**Sources:**
1. **SF Symbols** (primary choice)
2. Custom icons (follow SF Symbols style)
3. Heroicons (alternative, similar style)
4. Lucide Icons (clean, consistent)

**AVOID:**
- FontAwesome (too varied)
- Material Icons (Android style)
- AI-generated icon packs
- Inconsistent stroke weights

### 10. Accessibility

**Must-Have:**
- VoiceOver support
- Dynamic Type support
- Sufficient color contrast
- Clear focus indicators
- Keyboard navigation
- Reduced motion option

**Testing:**
- Test dengan VoiceOver enabled
- Test dengan largest text size
- Test dengan reduced motion
- Test dengan grayscale mode

---

## Screen Size Support

### iPhone (2025)
```
iPhone 16 Pro Max: 440 x 956pt
iPhone 16 Pro: 402 x 874pt
iPhone 16: 393 x 852pt
iPhone SE: 375 x 667pt
```

**Strategy:**
- Design untuk smallest target (375pt width)
- Scale up untuk larger screens
- Test pada multiple sizes

### iPad
```
12.9": 1024 x 1366pt
11": 834 x 1194pt
```

**Adaptations:**
- Guna sidebars
- Multi-column layouts
- Larger touch targets
- More whitespace

---

## Implementation Checklist

### Phase 1: Foundation
- [ ] Setup color system (light + dark)
- [ ] Define typography scale
- [ ] Create spacing tokens
- [ ] Setup icon library (SF Symbols)

### Phase 2: Components
- [ ] Buttons (all variants)
- [ ] Form inputs
- [ ] Cards
- [ ] Navigation bars
- [ ] Tab bars
- [ ] Modals/sheets

### Phase 3: Liquid Glass
- [ ] Implement backdrop-filter
- [ ] Add translucency effects
- [ ] Dynamic light/dark adaptation
- [ ] Specular highlights
- [ ] Motion responses

### Phase 4: Polish
- [ ] Animations & transitions
- [ ] Loading states
- [ ] Empty states
- [ ] Error states
- [ ] Success feedback

### Phase 5: Testing
- [ ] Light/dark mode
- [ ] Multiple screen sizes
- [ ] Accessibility audit
- [ ] Performance check
- [ ] User testing

---

## Resources

### Official Apple
- [Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/)
- [SF Symbols](https://developer.apple.com/sf-symbols/)
- [Apple Design Resources](https://developer.apple.com/design/resources/)

### Design Tools
- Figma: Apple iOS UI Kit
- Sketch: iOS UI Design Kit
- Icon Composer: Liquid Glass icons

### Inspiration
- Apple's native apps (Settings, Photos, Music)
- iOS 26 system UI
- macOS Tahoe 26 interface

---

## Anti-Patterns (JANGAN BUAT)

❌ Terlalu banyak colors dalam satu screen
❌ Icon yang inconsistent style
❌ Gradient yang arbitrary/random
❌ Shadow yang terlalu heavy
❌ Animation yang slow atau jarring
❌ Text yang too small (<11pt)
❌ Touch targets <44pt
❌ Ignore dark mode
❌ Flat design tanpa hierarchy
❌ Copy Android Material Design

---

## Summary

Design kita follow Apple's approach:
1. **Simple & Clean** - Kurang adalah lebih
2. **Content First** - UI support content, bukan distract
3. **Consistent** - Predictable patterns
4. **Accessible** - Untuk semua orang
5. **Delightful** - Subtle animations & polish

**Key Takeaway**: Guna SF Symbols, implement Liquid Glass, maintain consistency, dan test extensively dalam light & dark mode.

---

*Last Updated: April 2026*
*Based on: iOS 26 & Liquid Glass Design Language*
