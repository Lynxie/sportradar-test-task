# sportradar-test-task
Sportradar - Coding Exercise - Senior Software Developer

## Running tests

First, clone the repository:
```text
git clone https://github.com/Lynxie/sportradar-test-task
cd sportradar-test-task
```

**OPTION 1**: Running via Docker
```text
docker compose up --build
docker exec -it sportradar-test-task-php-1 /bin/bash
cd /var/www/html
```
**OPTION 2**: Running in a PHP 8.2+ Environment

If you have PHP 8.2 or higher, you can work directly in the project's "src" directory without Docker:
```text
cd <PROJECT_DIR>
cd src
```

Common Steps for Both Options
```text
composer install
./vendor/bin/phpunit
```

## Notes
You wanted to track the progress of the task, so I made more commits than I would in a real-world scenario. I assume the main criterion is that tests are written before implementation.

Everyone has their own perspective on coding. I always try to make code simpler, but this often depends on company practices, context, team size, business requirements, etc. I tried to balance simplicity with functionality.

I did not cover the FootballMatch class with separate tests, nor did I mock it when testing Scoreboard, they are tested together. This could be done, but in my opinion, it would overly complicate the tests in this context. Here, the KISS principle applies.
I moved store to a separate class with interface (you can see this in the commit history, and it could be changed back to an array in Scoreboard). This felt like the right approach. Since it's just an in-memory array, I don't think it makes sense to mock or test it separately here. Let me know if you have any feedback on this

Some tests are controversial from a business logic perspective. I left a comment, for example, in ScoreboardTest::testScoreCannotBeReduced.
I hope that sorting by match start time with second-level accuracy is acceptable (sorting by timestamp).


<em>**I really look forward to any feedback, regardless of the outcome**</em>