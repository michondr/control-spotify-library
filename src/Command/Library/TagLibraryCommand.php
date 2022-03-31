<?php

declare(strict_types = 1);

namespace App\Command\Library;

use App\Entity\CacheItem\CacheItem;
use App\Entity\CacheItem\CacheItemRepository;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagRepository;
use App\Entity\Track\Track;
use App\Entity\Track\TrackFacade;
use App\Entity\Track\TrackRepository;
use App\Entity\User\User;
use App\Entity\User\UserRepository;
use App\Spotify\SpotifyRepository;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'csl:tag:load-by-playlists-for-user',
    description: 'Sends mail to all providers to check if offers are still available',
)]
class TagLibraryCommand extends Command
{

    private const USERNAME = 'username';

    private SymfonyStyle $io;

    public function __construct(
        private UserRepository $userRepository,
        private SpotifyRepository $spotifyRepository,
        private SpotifyWebAPI $spotifyWebAPI,
        private TagRepository $tagRepository,
        private LoggerInterface $logger,
        private TrackFacade $trackFacade
    )
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument(self::USERNAME, InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $userName = $input->getArgument(self::USERNAME);
        $user = $this->userRepository->findByName($userName);

        if ($user === null) {
            $this->io->error(sprintf('user "%s" does not exist', $userName));

            return 1;
        }

        $this->spotifyWebAPI->setAccessToken($user->getAccessToken());

        foreach ($this->spotifyRepository->getPlaylists($user->getAccessToken()) as $playlist) {
            $this->logger->info('working on '.$playlist->name);
            $this->io->info(sprintf('processing playlist %s', $playlist->name));
            $this->processPlaylist($playlist->id, $playlist->name, $user);
        }

        return 0;
    }

    private function processPlaylist(string $id, string $playlistName, User $user): void
    {
        $playlistTracks = $this->spotifyRepository->getPlaylistTracks($id, $user->getAccessToken());

        $this->io->writeln(sprintf('has %d tracks', count($playlistTracks)));

        foreach ($playlistTracks as $playlistTrack) {
            $playlistTag = $this->tagRepository->findByNameAndUser($playlistName, $user);

            if ($playlistTag === null) {
                $playlistTag = new Tag($playlistName, $user);
                $this->tagRepository->save($playlistTag);
            }

            $this->processTrack($playlistTrack->track, $playlistTag);
        }
    }

    private function processTrack(object $spotifyTrack, Tag $playlistTag)
    {
        if ($spotifyTrack->id === null) {
            $this->logger->error('track has no id', ['track' => $spotifyTrack->uri]);

            return;
        }

        $track = $this->trackFacade->saveSpotifyTrack($spotifyTrack);

        $playlistTag->addTrack($track);
        $this->tagRepository->save($playlistTag);
    }
}