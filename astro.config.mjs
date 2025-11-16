// @ts-check
import tailwindcss from "@tailwindcss/vite";
import { defineConfig } from "astro/config";

import sitemap from "@astrojs/sitemap";

// https://astro.build/config
export default defineConfig({
  output: "static",
  site: "https://camping-kshurta.ru",

  vite: {
    plugins: [tailwindcss()],
    server: {
      proxy:
        process.env.NODE_ENV === "development"
          ? {
              "/api": {
                target: "http://localhost:8000",
                changeOrigin: true,
              },
            }
          : {},
    },
  },

  integrations: [sitemap()],
});