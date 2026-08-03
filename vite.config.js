import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import fullReload from 'vite-plugin-full-reload';

const themeRoot = fileURLToPath(new URL('.', import.meta.url));
const mainEntry = path.resolve(themeRoot, 'src/main.js');

// Vite builds only the main theme bundle (src/ → dist/). The editor blocks under
// /blocks are compiled separately by wp-scripts (`npm run build:blocks` → build/),
// which owns their JSX transform and block.json/render.php copying. Keep the two
// pipelines apart: Vite must not try to bundle block source, or it fails parsing
// JSX in .js entry files.
export default defineConfig({
  // Use ./src as the project root for dev/build.
  root: 'src',
  plugins: [
    fullReload([
      '**/*.php',
      '../**/*.php',
    ]),
  ],
  server: {
    // Local dev server port.
    port: 5175,
    strictPort: true,
  },
  css: {
    // Enable CSS sourcemaps in dev (build maps handled by Sass CLI).
    devSourcemap: true,
    preprocessorOptions: {
      scss: {
        // Allow Sass to emit source maps during dev.
        sourceMap: true,
        sourceMapIncludeSources: true,
      },
    },
  },
  build: {
    // Emit build output into the theme's /dist folder.
    outDir: '../dist',
    emptyOutDir: true,
    // Keep JS sourcemaps for debugging.
    sourcemap: true,
    rollupOptions: {
      // Use WordPress-provided packages at runtime instead of bundling them.
      external: [
        '@wordpress/blocks',
        '@wordpress/block-editor',
        '@wordpress/components',
        '@wordpress/element',
      ],
      input: {
        main: mainEntry,
      },
      output: {
        entryFileNames: 'main.js',
        chunkFileNames: 'chunks/[name].js',
        assetFileNames: 'assets/[name][extname]',
      },
    },
  },
});
