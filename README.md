# 🔏 Laravel Password Security Audit

This package provides an Artisan command to audit the security of your users' passwords.

Laravel Password Security Audit works by executing a long running process that checks your users passwords against
a list of over 10k commonly used weak passwords. When complete, it outputs a report of 
those users that are affected and the passwords that were found. 

## Installation

To install Laravel Password Security Audit, just run the following command from the
root of your project.

```bash
composer require jord-jd/laravel-password-security-audit
```

## Usage

In a standard Laravel installation using the default `\App\User` model, you can just 
run the `security:password-audit` Artisan command.

```bash
php artisan security:password-audit
```

While running a progress bar will be displayed indicating which user is being checked,
and an estimate of how long the process will take to complete. 

The speed of this process will take is dependent on the number of users your project 
has and your server's CPU performance. Multiple CPU cores will be taken advantage of 
if available.   

```
User 1   3.6%   33/560168   ETC: 4h 39m   Elapsed: 6s   ▓░░░░░░░░░░░░░░░░░░░  
```

When complete, you will be presented with a table of users with weak passwords.
For each user, this will include the user's primary key (usually the `id` field), 
the password found and its associated hash.

```   
6 user password(s) were found to be weak.
+----------+----------+--------------------------------------------------------------+
| Key (id) | Password | Hash                                                         |
+----------+----------+--------------------------------------------------------------+
| 1        | password | $2y$10$v6LjwoJOqumnO2A1VmscD.Tnot0D2koOzpGsmVfZaiWM6zprRpwWi |
| 2        | secret   | $2y$10$em9DONupJiDO1LMnR2PZZeoTOEyNutx4mGscQiKXWCBr09INUAjj6 |
| 14       | admin    | $2y$10$Kc.6/37NfY.D.JlSFxhyKexUQoo8dDng37MQDl.jSTtwclt7/ypJO |
| 43       | test123  | $2y$10$Nli8PgRNgTEZE1D1XuiBwOVdxRJJfkVvnWf7N2.Ko93av1ykC4DJO |
| 54       | secret   | $2y$10$eq6kcNOFC4bYNBDPHOTtC.EAvrQU3IK1kM5/QpwN3FK7HnxPOjR5e |
| 68       | secret   | $2y$10$Fvl47D2y0uDEr.6waoXzpeyB2k/.nz1SBlygWP12g8TbMEMxp1E4S |
+----------+----------+--------------------------------------------------------------+
``` 

### Custom user model

If you've moved the `User` model, or want to check a different model, you can use
the `--user-model` option. See the following example.

```bash
php artisan security:password-audit --user-model=\\App\\Models\\User
```

### Custom password field

If the passwords you wish to check are stored in a different field, you can change
this using the `--password-field` option. See the example below.

```bash
php artisan security:password-audit --password-field=new_password
```
