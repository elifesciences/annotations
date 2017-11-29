<?php

namespace eLife\Annotations\Command;

use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Profile;
use eLife\Bus\Command\QueueCommand;
use eLife\Bus\Limit\Limit;
use eLife\Bus\Queue\QueueItem;
use eLife\Bus\Queue\QueueItemTransformer;
use eLife\Bus\Queue\WatchableQueue;
use eLife\HypothesisClient\ApiSdk;
use eLife\HypothesisClient\Model\User;
use eLife\Logging\Monitoring;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;

final class QueueWatchCommand extends QueueCommand
{
    private $hypothesisSdk;

    public function __construct(
        WatchableQueue $queue,
        QueueItemTransformer $transformer,
        ApiSdk $hypothesisSdk,
        LoggerInterface $logger,
        Monitoring $monitoring,
        Limit $limit,
        bool $serializedTransform = false
    ) {
        parent::__construct($logger, $queue, $transformer, $monitoring, $limit, $serializedTransform);
        $this->hypothesisSdk = $hypothesisSdk;
    }

    protected function configure()
    {
        $this
            ->setName('queue:watch')
            ->setDescription('Create queue watcher')
            ->setHelp('Creates process that will watch for incoming items on a queue');
    }

    protected function process(InputInterface $input, QueueItem $item, $entity = null)
    {
        if ($entity instanceof Profile) {
            $id = $entity->getIdentifier()->getId();
            $emails = $entity
                ->getEmailAddresses()
                ->filter(function (AccessControl $accessControl) {
                    return $accessControl->getAccess() === AccessControl::ACCESS_PUBLIC;
                })
                ->map(function (AccessControl $accessControl) {
                    return $accessControl->getValue();
                });
            $display_name = $entity->getDetails()->getPreferredName();
            if (count($emails) > 0) {
                $email = $emails[0];
            } else {
                $this->logger->info(sprintf('No email address for profile "%s", backup email address created.', $id));
                $email = $id.'@blackhole.elifesciences.org';
            }
            if (strlen($display_name) > User::DISPLAY_NAME_MAX_LENGTH) {
                $sanitized_display_name = substr($display_name, 0, User::DISPLAY_NAME_MAX_LENGTH);
                $this->logger->info(sprintf('The display name for profile "%s" is too long and has been truncated from "%s" to "%s".', $id, $display_name, $sanitized_display_name));
            } else {
                $sanitized_display_name = $display_name;
            }
            $user = new User($id, $email, $sanitized_display_name);
            $upsert = $this->hypothesisSdk->users()->upsert($user)->wait();
            $this->logger->info(sprintf('Hypothesis user "%s" successfully %s.', $upsert->getUsername(), ($upsert->isNew() ? 'created' : 'updated')));
        }
    }
}
