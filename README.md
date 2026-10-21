# symfony-user-demo

Version: 8.0.2

## The code by mail

The demo accounts' addresses are `@example.com`: they receive nothing, and the code sent at sign-in is read only in the development mailbox. So the accounts sign in with their password alone unless the application asks for the code, where the mailbox exists:

```yaml
when@dev:
  wexample_symfony_user_demo:
    email_two_factor: true
```

Taken on the next reset of the accounts. The page marks what only the development shows (`marker('dev')`): the code, the magic link and the forgotten password, both of which send a mail — shown in dev and test only.

## Table of Contents

- [The code by mail](#the-code-by-mail)
- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

## Dependencies

- php: >=8.5
- wexample/symfony-helpers: >=18.0.0
- wexample/symfony-loader: >=25.0.0
- wexample/symfony-design-system: >=39.0.0
- wexample/symfony-design-system-demo: >=11.1.0
- wexample/symfony-routing: >=2.2.0
- wexample/symfony-user: >=14.0.0
- wexample/symfony-user-ds: >=5.1.0
- wexample/symfony-mail-ds: >=2.2.0
- wexample/symfony-organization: >=3.0.0
- wexample/symfony-search: >=4.2.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

Built and maintained by [Romain Weeger](https://weeger.io).

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
