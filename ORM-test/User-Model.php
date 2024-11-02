<?php

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    <?php
    use Doctrine\ORM\Mapping as ORM;
    
    /**
     * @ORM\Entity(repositoryClass="AccountRepository")
     * @ORM\Table(name="accounts")
     */
    class Account
    {
        /** @ORM\Id @ORM\Column(type="string", length=255) */
        private $userName;
        /** @ORM\Column(type="string", length=255) */
        private $passWord;
        /** @ORM\Column(type="datetime") */
        private $dateCreated;
        /** @ORM\Column(type="string", length=50) */
        private $accountStatus;
        /** @ORM\Column(type="string", length=255) */
        private $name;
        /** @ORM\Column(type="string", length=255) */
        private $address;
        /** @ORM\Column(type="string", length=255) */
        private $email;
        /** @ORM\Column(type="string", length=10) */
        private $phoneNumber;
        /** @ORM\Column(type="date") */
        private $birth;
        /** @ORM\Column(type="string", length=10) */
        private $sex;
        /** @ORM\Column(type="string", length=255) */
        private $codePermissions;

        // Getters and setters for each property
        public function getUserName() { return $this->userName; }
        public function setUserName($userName) { $this->userName = $userName; }
    
        public function getPassword() { return $this->passWord; }
        public function setPassword($password) { $this->passWord = password_hash($password, PASSWORD_BCRYPT); }
        // ... Other getters and setters
    }      

    class AccountRepository extends EntityRepository
    {
        public function deleteByID($userName)
        {
            $account = $this->findOneBy(['userName' => $userName]);

            if ($account) {
                $this->_em->remove($account);
                $this->_em->flush();
                return true;
            }
            return false;
        }

        public function findAllAccounts()
        {
            return $this->findAll();
        }

        public function findAccountByUserName($userName)
        {
            return $this->findOneBy(['userName' => $userName]);
        }

        public function addAccount($data)
        {
            $account = new Account();
            $account->setUserName($data['userName']);
            $account->setPassword($data['password']);
            $account->setDateCreated(new \DateTime());
            // Set other fields...

            $this->_em->persist($account);
            $this->_em->flush();
            return $account;
        }

        public function updateAccount(Account $account, $data)
        {
            if (isset($data['password'])) {
                $account->setPassword($data['password']);
            }
            // Update other fields...

            $this->_em->flush();
            return $account;
        }

        public function toggleAccountStatus($userName)
        {
            $account = $this->findAccountByUserName($userName);
            if ($account) {
                $newStatus = $account->getAccountStatus() === '1' ? '0' : '1';
                $account->setAccountStatus($newStatus);
                $this->_em->flush();
                return true;
            }
            return false;
        }
    }
    
}
