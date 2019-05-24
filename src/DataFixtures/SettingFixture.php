<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SettingFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $create = function ($name, $value) use ($manager) {
            $setting = new Setting();
            $setting->setName($name);
            $setting->setValue($value);
            $manager->persist($setting);
        };
        $create(Setting::INTERSTITIAL_DELAY, 5);
        $nullables = [
            Setting::TRACKING_SCRIPTS_BODY,
            Setting::TRACKING_SCRIPTS_HEAD,
        ];
        foreach ($nullables as $nullable) {
            $create($nullable, null);
        }
        $manager->flush();
    }
}
