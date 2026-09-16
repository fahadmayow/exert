import { defineConfig } from "vitepress";

export default defineConfig({
  base: "/exert/",
  title: "Exert",
  description:
    "One operation, one clear home. Learn the opinionated, HTTP-oriented action package for Laravel.",
  lang: "en-US",
  head: [
    [
      "link",
      {
        rel: "icon",
        type: "image/svg+xml",
        href: "/exert/favicon.svg",
      },
    ],
    [
      "meta",
      {
        name: "theme-color",
        content: "#ff2d20",
      },
    ],
  ],
  themeConfig: {
    logo: "/favicon.svg",
    nav: [
      {
        text: "Guide",
        link: "/start/what-is-exert",
        activeMatch: "^/(start|concepts|middleware|precognition|testing)/",
      },
      {
        text: "Practical guides",
        link: "/guides/order-workflow",
        activeMatch: "^/guides/",
      },
      {
        text: "Reference",
        link: "/reference/configuration",
        activeMatch: "^/(reference|commands)/",
      },
    ],
    sidebar: [
      {
        text: "Start here",
        collapsed: false,
        items: [
          {
            text: "Meet Exert",
            link: "/start/what-is-exert",
          },
          {
            text: "Installation",
            link: "/start/installation",
          },
          {
            text: "Your first endpoint",
            link: "/start/first-action",
          },
        ],
      },
      {
        text: "Build with Exert",
        collapsed: false,
        items: [
          {
            text: "Action classes",
            link: "/concepts/actions",
          },
          {
            text: "Controllers and registration",
            link: "/concepts/controllers",
          },
          {
            text: "Routes",
            link: "/concepts/routes",
          },
          {
            text: "Choosing an action",
            link: "/concepts/selection",
          },
          {
            text: "HTTP methods",
            link: "/concepts/http-methods",
          },
          {
            text: "Dependency injection",
            link: "/concepts/dependencies",
          },
          {
            text: "Validation",
            link: "/concepts/validation",
          },
          {
            text: "Authorization",
            link: "/concepts/authorization",
          },
          {
            text: "Route parameters",
            link: "/concepts/route-parameters",
          },
          {
            text: "Responses",
            link: "/concepts/responses",
          },
        ],
      },
      {
        text: "Middleware",
        collapsed: false,
        items: [
          {
            text: "Shared route middleware",
            link: "/middleware/routes",
          },
          {
            text: "Action middleware",
            link: "/middleware/actions",
          },
          {
            text: "Middleware order",
            link: "/middleware/order",
          },
          {
            text: "Middleware exclusions",
            link: "/middleware/exclusions",
          },
          {
            text: "Middleware lifecycle limits",
            link: "/middleware/lifecycle",
          },
        ],
      },
      {
        text: "Precognition",
        collapsed: false,
        items: [
          {
            text: "Meet Precognition",
            link: "/precognition/overview",
          },
          {
            text: "Enable Precognition",
            link: "/precognition/setup",
          },
          {
            text: "Try a prediction",
            link: "/precognition/requests",
          },
          {
            text: "What executes during prediction",
            link: "/precognition/execution",
          },
        ],
      },
      {
        text: "Practical guides",
        collapsed: false,
        items: [
          {
            text: "An order workflow",
            link: "/guides/order-workflow",
          },
          {
            text: "Signed URLs",
            link: "/guides/signed-urls",
          },
          {
            text: "Per-action rate limits",
            link: "/guides/rate-limits",
          },
          {
            text: "Logging and metadata",
            link: "/guides/logging",
          },
        ],
      },
      {
        text: "Testing",
        collapsed: false,
        items: [
          {
            text: "Test the endpoint",
            link: "/testing/http",
          },
          {
            text: "Test predictions",
            link: "/testing/precognition",
          },
        ],
      },
      {
        text: "Artisan commands",
        collapsed: false,
        items: [
          {
            text: "make:action",
            link: "/commands/make-action",
          },
          {
            text: "make:action-controller",
            link: "/commands/make-controller",
          },
          {
            text: "exert:config",
            link: "/commands/config",
          },
          {
            text: "exert:list",
            link: "/commands/list-actions",
          },
          {
            text: "exert:messages",
            link: "/commands/messages",
          },
          {
            text: "Custom templates",
            link: "/commands/templates",
          },
        ],
      },
      {
        text: "Reference",
        collapsed: false,
        items: [
          {
            text: "Configuration reference",
            link: "/reference/configuration",
          },
          {
            text: "Error reference",
            link: "/reference/errors",
          },
          {
            text: "Direct calls",
            link: "/reference/direct-calls",
          },
          {
            text: "Custom action implementations",
            link: "/reference/custom-actions",
          },
          {
            text: "Request lifecycle",
            link: "/reference/lifecycle",
          },
          {
            text: "Internals",
            link: "/reference/internals",
          },
          {
            text: "Troubleshooting",
            link: "/reference/troubleshooting",
          },
        ],
      },
      {
        text: "Contributing",
        collapsed: false,
        items: [
          {
            text: "Package development",
            link: "/contributing/development",
          },
        ],
      },
    ],
    outline: {
      level: [2, 3],
      label: "On this page",
    },
    search: {
      provider: "local",
    },
    socialLinks: [
      {
        icon: "github",
        link: "https://github.com/fahadmayow/exert",
      },
    ],
    docFooter: {
      prev: "Previous lesson",
      next: "Keep reading",
    },
    footer: {
      message:
        "An opinionated Laravel package. Released under the MIT license.",
      copyright: "Exert \u00b7 Small actions. Clear intent.",
    },
  },
});
