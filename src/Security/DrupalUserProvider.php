<?php

namespace MakinaCorpus\Drupal\Sf\Security;

use Drupal\Core\Entity\EntityManager as DrupalEntityManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DrupalUserProvider implements UserProviderInterface
{
    private $entityManager;

    /**
     * Default constructor
     *
     * @param DrupalEntityManager $entityManager
     */
    public function __construct(DrupalEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritoc}
     */
    public function loadUserByUsername($username)
    {
        $users = $this->entityManager->getStorage('user')->loadByProperties([
            'name' => $username,
        ]);

        if (!$users) {
            throw new UsernameNotFoundException();
        }

        return new DrupalUser(reset($users));
    }

    /**
     * {@inheritoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritoc}
     */
    public function supportsClass($class)
    {
        return DrupalUser::class === $class;
    }
}
