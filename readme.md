# Authorization

Základní rozdělení requirementů je na bezpředmětné (Requirement), předmětné (SubjectRequirement) a role (RoleRequirement).

### Requirement

```php
final readonly class CanCreateArticle implements Requirement {}
```

**Voter**

```php
final readonly class ArticleVoter implements Voter {

    #[VoteMethod]
    public function voteOnCreate(CanCreateArticle $requirement): bool {}

}
```

**Použití**
```php
/** @var UserRequirementChecker $requirementChecker */

$requirementChecker->isSatisfied($userIdentity, new CanCreateArticle());
```

### SubjectRequirement

```php
final readonly class CanEditArticle implements SubjectRequirement
{
    public function __construct(
        public Article $article,
    ) {}
}
```

**Voter**

```php
final readonly class ArticleVoter implements Voter {

    #[VoteMethod]
    public function voteOnEdit(CanEditArticle $requirement): bool 
    {
        return $this->canEdit($requirement->article);
    }

}
```

**Použití**
```php
/** @var UserRequirementChecker $requirementChecker */

$requirementChecker->isSatisfied($userIdentity, new CanEditArticle($article));
```

### RoleRequirement

```php
final readonly class HasAdminRole implements RoleRequirement
{
    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }
}
```

**Použití**
```php
/** @var UserRequirementChecker $requirementChecker */

$requirementChecker->isSatisfied($userIdentity, new HasAdminRole());
```

## Voter
Každá vote methoda musí být označena atributem `#[VoteMethod]`. Tímto způsobem je možné přidat další metody pro různé typy requirementů.

Každá vote methoda musí mít jako první non-nullable parametr `Requirement`, může se jednat i o union Requirementů.

```php
final readonly class ArticleVoter implements Voter {

    #[VoteMethod]
    public function voteOn(CanEditArticle|CanCreateArticle $requirement): bool 
    {
        return true;
    }

}
```

Je možné si vyžádat i uživatelské třídy jako `User`, `UserIdentity`. Které můžou být nullable. Jakmile jsou označený jako non-nullable a uživatel není přihlášený, tak se bere výsledek jako **false** bez volaní metody.

Dalším výběrem jsou služby jako `UserRequirementChecker`, `VoterContext` nebo vlastní služba rozšířující třídu `VoterService`.

```php
final readonly class ArticleVoter implements Voter {

    #[VoteMethod]
    public function voteOn(
        CanEditArticle|CanCreateArticle $requirement,
        User $user,
        UserIdentity $userIdentity,
        UserRequirementChecker $userRequirementChecker,
        VoterContext $voterContext,
        MyService $service,
    ): bool 
    {
        return true;
    }

}
```

## Context třídy
Třídy, které se používají v rámci nějakého kontextu (např. http requestu).

```php
final readonly class ArticleController {

    public function __construct(
        private CurrentUserContext $currentUserContext,
        private CurrentUserRequirementCheckerContext $currentUserRequirementCheckerContext,
    ) {}

}
```

### CurrentUserContext
Třída, která vrací aktuálního uživatele. Její implementace by měla být v rámci frameworku (např. Symfony) a měla by být schopná vrátit aktuálního uživatele na základě session, tokenu apod.

### CurrentUserRequirementCheckerContext
Třída, která kontroluje splnění requirementů pro aktuálního uživatele. Její implementace by měla být v rámci frameworku (např. Symfony) a měla by být schopná vrátit true/false na základě splnění requirementů pro aktuálního uživatele.
