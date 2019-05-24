<?php

namespace App\Controller;

use App\Entity\Click;
use App\Entity\Link;
use App\Entity\User;
use Cake\Chronos\Chronos;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Contracts\Cache\CacheInterface;

trait Charts
{
    /**
     * @param CacheInterface $cache
     * @param User|null $user
     * @throws \Psr\Cache\CacheException
     */
    protected function clearCache(CacheInterface $cache, ?User $user = null)
    {
        if ($user) {
            $cache->delete('popular-'.$user->getId());
            $cache->delete('summary-'.$user->getId());
        } else {
            $cache->delete('popular');
            $cache->delete('summary');
        }
    }

    protected function createPopularChart(CacheInterface $cache): array
    {
        $admin = $this->isGranted('ROLE_ADMIN');
        /** @var User $user */
        $user = $this->getUser();
        $key = $admin ? 'popular' : 'popular-'.$user->getId();
        return $cache->get($key, function () use ($admin, $user) {
            /** @var ObjectManager $manager */
            $manager = $this->getDoctrine()->getManager();
            $repo = $manager->getRepository(Link::class);
            $query = $repo->createQueryBuilder('l')
                ->select('l.slug AS slug')
                ->leftJoin('l.clicks', 'c')
                ->addSelect('COUNT(c.id) AS clicks')
                ->groupBy('l.id')
                ->orderBy('clicks', 'DESC');
            if (!$admin) {
                $query->where('l.user = :user')
                    ->setParameter('user', $user);
            }
            $result = $query->setMaxResults(5)
                ->getQuery()
                ->getResult();
            $labels = [];
            $series = [];
            foreach ($result as $item) {
                $labels[] = $item['slug'];
                $series[] = $item['clicks'];
            }
            return [
                'labels' => $labels,
                'series' => [$series],
            ];
        });
    }

    protected function createSummaryChart(CacheInterface $cache): array
    {
        $admin = $this->isGranted('ROLE_ADMIN');
        /** @var User $user */
        $user = $this->getUser();
        $key = $admin ? 'summary' : 'summary-'.$user->getId();
        return $cache->get($key, function () use ($admin, $user) {
            /** @var ObjectManager $manager */
            $manager = $this->getDoctrine()->getManager();
            $day7 = Chronos::today();
            $day6 = $day7->subDay();
            $day5 = $day6->subDay();
            $day4 = $day5->subDay();
            $day3 = $day4->subDay();
            $day2 = $day3->subDay();
            $day1 = $day2->subDay();
            $repo = $manager->getRepository(Click::class);
            $query = $repo->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.createdAt BETWEEN :from AND :upto');
            if (!$admin) {
                $query->leftJoin('c.link', 'l')
                    ->andWhere('l.user = :user')
                    ->setParameter('user', $user);
            }
            return [
                'labels' => [
                    $day1->format('D'),
                    $day2->format('D'),
                    $day3->format('D'),
                    $day4->format('D'),
                    $day5->format('D'),
                    $day6->format('D'),
                    $day7->format('D'),
                ],
                'series' => [[
                    (int) (clone $query)->setParameter('from', $day1)
                        ->setParameter('upto', $day1->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                    (int) (clone $query)->setParameter('from', $day2)
                        ->setParameter('upto', $day2->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                    (int) (clone $query)->setParameter('from', $day3)
                        ->setParameter('upto', $day3->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                    (int) (clone $query)->setParameter('from', $day4)
                        ->setParameter('upto', $day4->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                    (int) (clone $query)->setParameter('from', $day5)
                        ->setParameter('upto', $day5->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                    (int) (clone $query)->setParameter('from', $day6)
                        ->setParameter('upto', $day6->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                    (int) (clone $query)->setParameter('from', $day7)
                        ->setParameter('upto', $day7->setTime(23, 59, 59))
                        ->getQuery()
                        ->getSingleScalarResult(),
                ]],
            ];
        });
    }
}
