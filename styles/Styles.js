/**
 * Nexify Styles Library
 * Comprehensive styling system based on Tailwind CSS with custom components
 */

// Export all style modules
export * from "./base/index.js";
export * from "./atoms/index.js";
export * from "./molecules/index.js";
export * from "./organisms/index.js";
export * from "./templates/index.js";
export * from "./utilities/index.js";
export * from "./animations/index.js";
export * from "./transitions/index.js";
export * from "./themes/index.js";

// CSS injection utility
export function injectStyles() {
  // Create or get style element
  let styleElement = document.getElementById("nexify-styles");
  if (!styleElement) {
    styleElement = document.createElement("style");
    styleElement.id = "nexify-styles";
    document.head.appendChild(styleElement);
  }

  // Base styles that are always needed
  const baseStyles = `
        /* Nexify Base Styles */
        .ripple-effect {
            position: relative;
            overflow: hidden;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Table striped styles */
        .table-striped tbody tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .dark .table-striped tbody tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.02);
        }

        /* Line clamp utilities */
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }

        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }

        /* Aspect ratio utilities */
        .aspect-3\\/2 {
            aspect-ratio: 3 / 2;
        }

        .aspect-4\\/3 {
            aspect-ratio: 4 / 3;
        }

        /* Custom scrollbar */
        .scrollbar-thin {
            scrollbar-width: thin;
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Focus visible styles */
        .focus-visible\\:ring-2:focus-visible {
            ring-width: 2px;
        }

        /* Custom transitions */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    `;

  styleElement.textContent = baseStyles;
}

// Auto-inject styles when module loads
if (typeof window !== "undefined") {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", injectStyles);
  } else {
    injectStyles();
  }
}
