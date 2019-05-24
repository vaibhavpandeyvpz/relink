<?php

namespace App\Controller;

use App\Entity\Link;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DomCrawler\Crawler;

trait Links
{
    protected function checkIfSlugExists(string $slug, ?Link $except = null): bool
    {
        $manager = $this->getDoctrine()->getManager();
        $repo = $manager->getRepository(Link::class);
        $query = $repo->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.slug = :slug')
            ->setParameter('slug', $slug);
        if ($except) {
            $query->andWhere('l.id != :id')
                ->setParameter('id', $except->getId());
        }
        $count = (int) $query->getQuery()
            ->getSingleScalarResult();
        return $count > 0;
    }

    protected function createUniqueSlug(): string
    {
        $manager = $this->getDoctrine()->getManager();
        $repo = $manager->getRepository(Link::class);
        $slug = str_random(8);
        while ($repo->findOneBy(compact('slug')) !== null) {
            $slug = str_random(8);
        }
        return $slug;
    }

    protected function crawlMetaTags(string $url): array
    {
        $description = $title = null;
        try {
            $response = (new Client())->get($url, [RequestOptions::TIMEOUT => 5]);
        } catch (RequestException $e) {
        }
        if (isset($response)) {
            $successful = $response->getStatusCode() === 200;
            $html = stripos($response->getHeaderLine('Content-Type'), 'text/html') === 0;
            if ($successful && $html) {
                $crawler = new Crawler((string) $response->getBody());
                $descriptions = $crawler->filterXpath("//meta[@name='description']")->extract(['content']);
                $description = empty($descriptions[0]) ? null : trim($descriptions[0]);
                if (strlen($description) > 0) {
                    $description = substr($description, 0, 255);
                }
                $nodes = $crawler->filter('title');
                $title = $nodes->count() >= 1 ? $nodes->first()->text() : null;
                if (strlen($title) > 0) {
                    $title = substr($title, 0, 255);
                }
            }
        }
        return compact('description', 'title');
    }
}
