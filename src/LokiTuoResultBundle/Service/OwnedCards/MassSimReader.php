<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 16.08.16
 * Time: 18:49.
 */

namespace LokiTuoResultBundle\Service\OwnedCards;

use Doctrine\ORM\EntityManager;
use Exception;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\OwnedCards\Service as OwnedCardManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class MassSimReader.
 *
 * @deprecated
 */
class MassSimReader
{
    /** @var EntityManager */
    private $em;

    /** @var OwnedCardManager */
    private $ownedCardManager;

    use LoggerAwareTrait;

    /**
     * MassSimReader constructor.
     *
     * @param EntityManager $entityManager
     * @param Service       $manager
     */
    public function __construct(EntityManager $entityManager, OwnedCardManager $manager)
    {
        $this->em               = $entityManager;
        $this->ownedCardManager = $manager;
        $this->logger           = new NullLogger();
    }

    /**
     * Create a Map with Players and Cards.
     *
     * @param $filePath
     *
     * @return array
     */
    public function getPlayerCardMap($filePath)
    {
        $content           = $this->getContentArray($filePath);
        $map               = [];
        $ownedCards        = [];
        $guild             = $this->getGuildName($content);
        $result            = ['players' => [], 'guild' => $guild];
        $currentPlayerName = '';
        foreach ($content as $line) {
            $match = [];
            preg_match('/MemberDeck(\d+)=/', $line, $match);
            if (count($match) == 2) {
                $ownedCards[$match[1]] = $this->transformOwnedCards($line);
            }

            preg_match('/echo "member name (.+)@/', $line, $match);
            if (count($match) == 2) {
                $currentPlayerName = $match[1];
                if (! isset($map[$currentPlayerName])) {
                    $map[$currentPlayerName] = [];
                }
            }

            if (strpos($line, './tuo') !== false) {
                $deck                    = $this->transformDeckCards($line);
                $map[$currentPlayerName] = array_merge($map[$currentPlayerName], $deck);
            }

            preg_match('/-o="\$MemberDeck(\d+)"/', $line, $match);
            if (count($match) == 2) {
                $playerId = $match[1];

                foreach ($map[$currentPlayerName] as $card) {
                    $key = $card['name'].$card['level'];
                    if (array_key_exists($key, $ownedCards[$playerId])) {
                        $ownedCards[$playerId][$key]['inDeck'] = $card['inDeck'];
                    }
                }
                foreach ($ownedCards[$playerId] as $card) {
                    $key                                         = $card['name'].$card['level'];
                    $result['players'][$currentPlayerName][$key] = $card;
                }
            }
        }

        return $result;
    }

    /**
     * Save Player Map.
     *
     * @param $map
     *
     * @return array
     */
    public function savePlayerCardMap($map)
    {
        $result = [];
        $guild  = $map['guild'];
        foreach ($map['players'] as $playerName => $cardArray) {
            $player = $this->findPlayerOrCreate($playerName, $guild);
            $this->logger->debug('Trying to persist '.count($cardArray).' cards for Player '.$player->getName());
            $result[$player->getName()] = $this->ownedCardManager->transformArrayToModels($player, $cardArray);
            $this->ownedCardManager->removeOldOwnedCardsForPlayer($player);
            foreach ($result[$player->getName()] as $card) {
                $this->em->persist($card);
            }
            $this->em->flush();
            $this->logger->debug('persisted '.count($result[$playerName]).' cards for Player '.$playerName);
        }
        $this->em->flush();

        return $result;
    }

    /**
     * Find or Create Player.
     *
     * @param $playerName
     * @param $guild
     *
     * @return Player|null|object
     */
    private function findPlayerOrCreate($playerName, $guild)
    {
        $playerRepo = $this->em->getRepository('LokiTuoResultBundle:Player');
        $player     = $playerRepo->findOneBy(['name' => $playerName]);
        if (! $player) {
            $this->logger->info("Created Player $playerName because no Player was found.");
            $player = new Player();
            $player->setName($playerName);
            $player->setCurrentGuild($guild);
            $this->em->persist($player);
        }

        return $player;
    }

    /**
     * @param $filePath
     *
     * @return array
     */
    private function getContentArray($filePath)
    {
        //TODO Check if file exists
        return explode("\n", file_get_contents($filePath));
    }

    /**
     * Transfrom Owned Cards String to an Array of Cardnames.
     *
     * @param $line
     *
     * @return array
     */
    private function transformOwnedCards($line)
    {
        $regEx  = '/MemberDeck\d+="(.+)"/';
        $inDeck = false;

        return $this->transformWithRegEx($line, $regEx, $inDeck);
    }

    /**
     * Transform Dekc Line.
     *
     * @param $line
     *
     * @return array
     */
    private function transformDeckCards($line)
    {
        $regEx  = '/\.\/tuo "(.*)" "/';
        $inDeck = true;

        return $this->transformWithRegEx($line, $regEx, $inDeck);
    }

    /**
     * Transform a String with the RegEx and return an Array of Cardnames.
     *
     * @param $line
     * @param $regEx
     * @param bool $inDeck
     *
     * @return array
     */
    private function transformWithRegEx($line, $regEx, $inDeck = false)
    {
        $owned   = [];
        $matches = [];
        preg_match($regEx, $line, $matches);
        $cards = explode(',', $matches[1]);
        foreach ($cards as $card) {
            $entry       = $this->ownedCardManager->transformCardString(trim($card), $inDeck);
            $key         = $entry['name'].$entry['level'];
            $owned[$key] = $entry;
        }

        return $owned;
    }

    /**
     * Return the name of the Guild for the Content.
     *
     * @param $content
     *
     * @throws Exception
     *
     * @return mixed|string
     */
    private function getGuildName($content)
    {
        $guild = [];
        if (preg_match('/([a-zA-z]+) Results/', $content[0], $guild) === 1) {
            //Special Case where the Guild CNS had the PRefix CTF
            //FIXME seems legacy now
            return ($guild[1] == 'CTF') ? 'CNS' : $guild[1];
        } else {
            $this->logger->error('No Correct Guild found in '.$content[0]);
            throw new Exception('No Guild Found');
        }
    }
}
