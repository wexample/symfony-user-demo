## The code by mail

The demo accounts' addresses are `@example.com`: they receive nothing, and the code sent at sign-in is read only in the development mailbox. So the accounts sign in with their password alone unless the application asks for the code, where the mailbox exists:

```yaml
when@dev:
  wexample_symfony_user_demo:
    email_two_factor: true
```

Taken on the next reset of the accounts. The page marks what only the development shows (`marker('dev')`): the code, the magic link and the forgotten password, both of which send a mail — shown in dev and test only.
