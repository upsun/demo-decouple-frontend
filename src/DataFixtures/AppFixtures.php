<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadTags($manager);
        $this->loadPosts($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);

            $manager->persist($user);
            $this->addReference($username, $user);
        }

        $manager->flush();
    }

    private function loadTags(ObjectManager $manager): void
    {
        foreach ($this->getTagData() as $name) {
            $tag = new Tag($name);

            $manager->persist($tag);
            $this->addReference('tag-'.$name, $tag);
        }

        $manager->flush();
    }

    private function loadPosts(ObjectManager $manager): void
    {
        foreach ($this->getPostData() as [$title, $slug, $summary, $content, $publishedAt, $author, $tags]) {
            $post = new Post();
            $post->setTitle($title);
            $post->setSlug($slug);
            $post->setSummary($summary);
            $post->setContent($content);
            $post->setPublishedAt($publishedAt);
            $post->setAuthor($author);
            $post->addTag(...$tags);

            foreach (range(1, 5) as $i) {
                /** @var User $commentAuthor */
                $commentAuthor = $this->getReference('john_user');

                $comment = new Comment();
                $comment->setAuthor($commentAuthor);
                $comment->setContent($this->getRandomText(random_int(255, 512)));
                $comment->setPublishedAt(new \DateTimeImmutable('now + '.$i.'seconds'));

                $post->addComment($comment);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }

    /**
     * @return array<array{string, string, string, string, array<string>}>
     */
    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
            ['Jane Doe', 'jane_admin', 'kitten', 'jane_admin@symfony.com', [User::ROLE_ADMIN]],
            ['Tom Doe', 'tom_admin', 'kitten', 'tom_admin@symfony.com', [User::ROLE_ADMIN]],
            ['John Doe', 'john_user', 'kitten', 'john_user@symfony.com', [User::ROLE_USER]],
        ];
    }

    /**
     * @return string[]
     */
    private function getTagData(): array
    {
        return [
            'lorem',
            'ipsum',
            'consectetur',
            'adipiscing',
            'incididunt',
            'labore',
            'voluptate',
            'dolore',
            'pariatur',
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array<int, array{0: string, 1: AbstractUnicodeString, 2: string, 3: string, 4: \DateTimeImmutable, 5: User, 6: array<Tag>}>
     */
    private function getPostData(): array
    {
        $posts = [];

        foreach ($this->getPhrases() as $i => $title) {
            // $postData = [$title, $slug, $summary, $content, $publishedAt, $author, $tags, $comments];

            /** @var User $user */
            $user = $this->getReference(['jane_admin', 'tom_admin'][0 === $i ? 0 : random_int(0, 1)]);

            $posts[] = [
                $title,
                $this->slugger->slug($title)->lower(),
                $this->getRandomText(),
                $this->getPostContent(),
                (new \DateTimeImmutable('now - '.$i.'days'))->setTime(random_int(8, 17), random_int(7, 49), random_int(0, 59)),
                // Ensure that the first post is written by Jane Doe to simplify tests
                $user,
                $this->getRandomTags(),
            ];
        }

        return $posts;
    }

    /**
     * @return string[]
     */
    private function getPhrases(): array
    {
        return [
            'Unlock Lightning-Fast Transactions: How Upsun Powers High-Volume Apps',
            'The API-First Revolution: Why Modern Teams Choose Upsun for Shared Services',
            'Decoupled Web UIs Made Easy: Upsun\'s Secret Sauce for Jamstack & SPAs',
            'Cloud Migration Without the Headaches: Upsun\'s Proven Path to Modernization',
            'AI Agents That Actually Work: Building Smarter Apps with Upsun',
            'Stop Flying Blind: How Upsun Delivers Real Monitoring & Observability',
        ];
    }

    private function getRandomText(int $maxLength = 255): string
    {
        $summaries = [
            'Discover how Upsun enables blazing-fast, resilient, and scalable transactional applications for the most demanding workloads.',
            'Learn how Upsun\'s API-first approach empowers microservices and shared services to scale with your business needs.',
            'Explore how Upsun helps you build interactive, decoupled web experiences using Jamstack, SPAs, and modern web architectures.',
            'See how Upsun streamlines cloud migration, from lift-and-shift to full modernization and replatforming.',
            'Find out how Upsun accelerates the development and deployment of AI agents and applications in real-world environments.',
            'Understand how developers use Upsun to monitor, manage, and gain deep observability into their applications.',
        ];
        static $i = 0;
        $summary = $summaries[$i % count($summaries)];
        $i++;
        return $summary;
    }

    private function getPostContent(): string
    {
        static $i = 0;
        $contents = [
            // High-volume transactional applications
            <<<MARKDOWN
**Is your app ready for a tidal wave of users?**

Upsun is purpose-built for high-volume transactional applications that demand speed, scalability, and resilience. Whether you're processing thousands of payments per second or handling real-time data streams, Upsun's cloud-native architecture ensures your workloads stay fast and reliable—no matter the scale.

- **Auto-scaling** to handle unpredictable spikes
- **Zero-downtime deployments** for continuous innovation
- **Built-in failover** and disaster recovery

> "With Upsun, we scaled from 10,000 to 1 million transactions per day—without a single hiccup."  
— CTO, Fintech Startup

Ready to future-proof your transactional workloads? [Learn more at Upsun.com](https://upsun.com)
MARKDOWN
            ,
            // API-first shared services
            <<<MARKDOWN
**Why are top teams going API-first?**

Upsun makes it easy to design, deploy, and manage shared services with an API-first mindset. Our platform is optimized for microservices architectures, letting you scale services up or down instantly and integrate with any stack.

- **OpenAPI & GraphQL** support out of the box
- **Service discovery** and versioning made simple
- **Seamless scaling** for every endpoint

> "Upsun's API-first tools let us launch new services in days, not weeks."  
— Lead Engineer, SaaS Provider

Build the backbone of your digital business with Upsun. [See how at Upsun.com](https://upsun.com)
MARKDOWN
            ,
            // Decoupled Web UI/UX
            <<<MARKDOWN
**Want a web experience users love?**

Upsun empowers you to build decoupled, interactive web UIs—whether you're using Jamstack, SPAs, or embedded mobile components. Deliver lightning-fast, responsive apps directly in the browser or on any device.

- **Progressive Web App (PWA) support**
- **Instant global delivery** via CDN
- **Developer-friendly workflows** for rapid iteration

> "Our Jamstack site on Upsun loads in under a second, anywhere in the world."  
— Product Manager, E-commerce

Delight your users with modern web experiences. [Get started at Upsun.com](https://upsun.com)
MARKDOWN
            ,
            // Cloud Migration
            <<<MARKDOWN
**Migrating to the cloud? Don't risk downtime!**

Upsun is your partner for seamless cloud migration and modernization. Move workloads, replatform, or rebuild with confidence—our platform supports every stage of your cloud journey.

- **Automated migration tools**
- **Support for lift-and-shift, modernization, and replatforming**
- **Unified management for hybrid and multi-cloud**

> "We migrated 50+ apps to the cloud with Upsun—on time and under budget."  
— IT Director, Enterprise Retail

Make your cloud move a success. [See migration solutions at Upsun.com](https://upsun.com)
MARKDOWN
            ,
            // AI Agents and Applications
            <<<MARKDOWN
**Ready to unleash AI in your business?**

Upsun accelerates the development and deployment of AI agents and applications. Build autonomous or semi-autonomous software that perceives, decides, and acts—powered by the latest AI techniques.

- **Integrated ML/AI pipelines**
- **Real-time data processing**
- **Secure, scalable deployment for AI workloads**

> "Upsun helped us launch AI-powered agents that automate 80% of our support tickets."  
— Head of Digital, Customer Service Platform

Bring your AI ideas to life. [Explore AI with Upsun.com](https://upsun.com)
MARKDOWN
            ,
            // Monitoring & Observability
            <<<MARKDOWN
**Are you flying blind in production?**

With Upsun, developers get real-time monitoring and deep observability for every app and service. Track performance, catch issues before users do, and get actionable insights—all in one place.

- **Unified dashboards** for all your apps
- **Proactive alerts** and anomaly detection
- **Seamless integration with popular observability tools**

> "We cut our incident response time in half with Upsun's monitoring tools."  
— DevOps Lead, SaaS Platform

Take control of your apps. [See observability in action at Upsun.com](https://upsun.com)
MARKDOWN
        ];
        $content = $contents[$i % count($contents)];
        $i++;
        return $content;
    }

    /**
     * @throws \Exception
     *
     * @return array<Tag>
     */
    private function getRandomTags(): array
    {
        $tagNames = $this->getTagData();
        shuffle($tagNames);
        $selectedTags = \array_slice($tagNames, 0, random_int(2, 4));

        return array_map(function ($tagName) {
            /** @var Tag $tag */
            $tag = $this->getReference('tag-'.$tagName);

            return $tag;
        }, $selectedTags);
    }
}
