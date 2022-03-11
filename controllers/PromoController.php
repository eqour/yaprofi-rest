<?php

namespace app\controllers;

use app\Application;
use app\helpers\RaffleHelper;
use PDO;
use PDOException;

class PromoController
{
    public function actionPost(): array
    {
        $data = Application::jsonDataFromBody();
        $route = Application::$route;

        if (isset($route[1]) && isset($route[2]) && $route[2] === 'prize' && count($route) === 3) {
            if (!isset($data['description'])) {
                return ['error' => 'description is required'];
            }

            $id = $route[1];
            $description = $data['description'];
            
            try {
                Application::$db->prepare("INSERT INTO `prize` (`description`, `promo_id`) VALUES (:description, :promo_id)")->execute(['description' => $description, 'promo_id' => $id]);
            } catch (PDOException $x) {
                http_response_code(405);
                return [];
            }
    
            http_response_code(201);
    
            $lastId = Application::$db->lastInsertId('prize');
            return ['id' => $lastId];
        } else if (isset($route[1]) && isset($route[2]) && $route[2] === 'participant' && count($route) === 3) {
            if (!isset($data['name'])) {
                return ['error' => 'name is required'];
            }

            $id = $route[1];
            $name = $data['name'];
            
            try {
                Application::$db->prepare("INSERT INTO `participant` (`name`, `promo_id`) VALUES (:name, :promo_id)")->execute(['name' => $name, 'promo_id' => $id]);
            } catch (PDOException $x) {
                http_response_code(405);
                return [];
            }
    
            http_response_code(201);
    
            $lastId = Application::$db->lastInsertId('participant');
            return ['id' => $lastId];
        } else if (isset($route[1]) && isset($route[2]) && $route[2] === 'raffle' && count($route) === 3) {
            $id = Application::$route[1];

            $stm = Application::$db->prepare("SELECT `prize`.`id`, `prize`.`description` FROM `prize`
            INNER JOIN `promo` ON `prize`.`promo_id` = `promo`.`id`
            WHERE `promo`.`id` = :id");
            $stm->execute(['id' => $id]);
            $prizes = $stm->fetchAll(PDO::FETCH_ASSOC);

            $stm = Application::$db->prepare("SELECT `participant`.`id`, `participant`.`name` FROM `participant`
                INNER JOIN `promo` ON `participant`.`promo_id` = `promo`.`id`
                WHERE `promo`.`id` = :id");
            $stm->execute(['id' => $id]);
            $participants = $stm->fetchAll(PDO::FETCH_ASSOC);

            if (count($participants) != count($prizes)) {
                http_response_code(409);
                return [];
            }

            return (new RaffleHelper())->process($participants, $prizes);
        } else if (isset($route[0]) && count($route) === 1) {
            if (!isset($data['name'])) {
                return ['error' => 'name is required'];
            }
    
            $name = $data['name'];
            $description = $data['description'] ?? null;
    
            Application::$db->prepare("INSERT INTO `promo` (`name`, `description`) VALUES (:name, :description)")->execute(['name' => $name, 'description' => $description]);
    
            http_response_code(201);
    
            $lastId = Application::$db->lastInsertId('promo');
            return ['id' => $lastId];
        } else {
            http_response_code(405);
            return [];
        }
    }

    public function actionGet(): array
    {
        if (isset(Application::$route[1]) && count(Application::$route) === 2) {
            $id = Application::$route[1];

            $stm = Application::$db->prepare("SELECT * FROM `promo` WHERE `promo`.`id` = :id");
            $stm->execute(['id' => $id]);
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                http_response_code(404);
                return [];
            }

            $response = $result[0];
            $response['prizes'] = [];
            $response['participants'] = [];

            $stm = Application::$db->prepare("SELECT `prize`.`id`, `prize`.`description` FROM `prize`
                INNER JOIN `promo` ON `prize`.`promo_id` = `promo`.`id`
                WHERE `promo`.`id` = :id");
            $stm->execute(['id' => $id]);
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);

            $response['prizes'] = $result;

            $stm = Application::$db->prepare("SELECT `participant`.`id`, `participant`.`name` FROM `participant`
                INNER JOIN `promo` ON `participant`.`promo_id` = `promo`.`id`
                WHERE `promo`.`id` = :id");
            $stm->execute(['id' => $id]);
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);

            $response['participants'] = $result;

            return $response;
        } else if (count(Application::$route) === 1) {
            $stm = Application::$db->prepare("SELECT * FROM `promo`");
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);
    
            return $result;
        } else {
            http_response_code(404);
            return [];
        }
    }

    public function actionPut(): array
    {
        if (isset(Application::$route[1]) && count(Application::$route) == 2) {
            $id = Application::$route[1];
            $data = Application::jsonDataFromBody();

            $stm = Application::$db->prepare("SELECT * FROM `promo` WHERE `promo`.`id` = :id");
            $stm->execute(['id' => $id]);
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                http_response_code(404);
                return [];
            }

            if (isset($data['name']) && $data['name'] != null) {
                $name = $data['name'];
            }

            if (isset($data['description'])) {
                $description = $data['description'];
            }

            if (isset($name) && !isset($description)) {
                Application::$db
                ->prepare("UPDATE `promo` SET `name` = :name WHERE `id` = :id")
                ->execute(['name' => $name, 'id' => $id]);
            } else if (!isset($name) && isset($description)) {
                Application::$db
                ->prepare("UPDATE `promo` SET `description` = :description WHERE `id` = :id")
                ->execute(['description' => $description, 'id' => $id]);
            } else if (isset($name) && isset($description)) {
                Application::$db
                ->prepare("UPDATE `promo` SET `name` = :name, `description` = :description WHERE `id` = :id")
                ->execute(['name' => $name, 'description' => $description, 'id' => $id]);
            }
            
            return [];
        } else {
            http_response_code(405);
            return [];
        }
    }

    public function actionDelete(): array
    {
        if (isset(Application::$route[1]) && count(Application::$route) == 2) {
            $id = Application::$route[1];
            Application::$db->prepare("DELETE FROM `promo` WHERE `id` = :id")->execute(['id' => $id]);
            return [];
        } else if (isset(Application::$route[1]) && isset(Application::$route[2]) && Application::$route[2] === 'participant' && isset(Application::$route[3]) && count(Application::$route) === 4) {
            $participantId = Application::$route[3];
            Application::$db->prepare("DELETE FROM `participant`
                WHERE `participant`.`id` = :participant_id")->execute(['participant_id' => $participantId]);
            return [];
        } else if (isset(Application::$route[1]) && isset(Application::$route[2]) && Application::$route[2] === 'prize' && isset(Application::$route[3]) && count(Application::$route) === 4) {
            $participantId = Application::$route[3];
            Application::$db->prepare("DELETE FROM `prize`
                WHERE `prize`.`id` = :prize_id")->execute(['prize_id' => $participantId]);
            return [];
        } else {
            http_response_code(405);
            return [];
        }
    }
}
