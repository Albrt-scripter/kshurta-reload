// @ts-check
import tailwindcss from "@tailwindcss/vite";
import { defineConfig } from "astro/config";

// https://astro.build/config
export default defineConfig({
  output: "static",
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
});
