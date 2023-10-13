<?php

namespace Controllers;

use Exception;
use Models\LoginModel;

require_once __DIR__ . '/../Models/LoginModel.php';

class LoginController
{
    private LoginModel $loginModel;
    private string $nicknameStatus = '';
    private array $validationErrors = [];

    public function __construct(LoginModel $loginModel)
    {
        $this->loginModel = $loginModel;
    }

    /**
     * Handles the user login request.
     *
     * @param array $postData Data received from the login form.
     *
     * @return array The result of processing the request.
     * @throws Exception
     */
    public function handleLoginRequest(array $postData): array
    {
        $login = $postData['inputLogin'];

        $this->validationNicknameProcess($login);

        if ($this->validationErrors) {
            return $this->handleInvalidNickname();
        }

        return $this->handleValidNickname($login);
    }

    /**
     * Handles a successful user login.
     *
     * @param string $login User's nickname.
     *
     * @return array Result of a successful login.
     * @throws Exception
     */
    private function handleValidNickname(string $login): array
    {
        $response = [];

        $response['userLogin'] = $login;
        $response['isAllowed'] = true;

        if ($this->nicknameStatus === 'offline') {
            $this->loginModel->updateLogin($login);
            $userId = $this->loginModel->selectLoginId($login)[0]['user_id'];

            $response['isInReconnect'] = $this->loginModel->selectStatus($userId, 2);
            return $response;
        }

        $this->registerUser($login);

        return $response;
    }

    /**
     * Handles an invalid nickname and returns validation errors.
     *
     * @return array Validation errors and "not allowed" status.
     */
    private function handleInvalidNickname(): array
    {
        return [
            'isAllowed' => false,
            'validationErrors' => $this->validationErrors,
        ];
    }

    /**
     * Performs the nickname validation process.
     *
     * @param string $nickname The nickname to validate.
     *
     * @throws Exception
     */
    private function validationNicknameProcess(string $nickname): void
    {
        // Perform various stages of nickname validation
        $this->validateLength($nickname);
        $this->validateCharacters($nickname);
        $this->validateStartingCharacter($nickname);
        $this->validateEndingCharacter($nickname);
        $this->validateNicknameUniqueness($nickname);
    }

    private function validateLength(string $nickname): void
    {
        $minLength = 3;
        $maxLength = 10;

        $userNicknameLength = mb_strlen($nickname, 'UTF-8');

        if ($userNicknameLength < $minLength || $userNicknameLength > $maxLength) {
            $this->validationErrors[] = "На жаль, помилка - дозволена довжина нікнейму від $minLength до $maxLength символів.";
        }
    }

    private function validateCharacters(string $nickname): void
    {
        $regExp = (bool)preg_match("/^[a-zA-Zа-яА-ЯіІєЄ0-9\s'\-_]+$/u", $nickname);

        if (false === $regExp) {
            $this->validationErrors[] =
                "На жаль, помилка - нікнейм може містити літери (zZ-яЯ), цифри (0-9), спецсимволи (пробел, -, ', _).";
        }
    }

    private function validateStartingCharacter(string $nickname): void
    {
        $regExp = (bool)preg_match('/^[a-zA-Zа-яА-ЯіІєЄ0-9]/u', $nickname);

        if (false === $regExp) {
            $this->validationErrors[] = "Нікнейм повинен починатися з літери чи цифри.";
        }
    }

    private function validateEndingCharacter(string $nickname): void
    {
        $regExp = (bool)preg_match('/[a-zA-Zа-яА-ЯіІєЄ0-9]$/u', $nickname);

        if (false === $regExp) {
            $this->validationErrors[] = "Нікнейм повинен закінчуватися на літеру чи цифру.";
        }
    }

    /**
     * @throws Exception
     */
    private function validateNicknameUniqueness(string $nickname): void
    {
        if ($this->loginModel->isNicknameTaken($nickname, 1)) {
            $this->validationErrors[] = "На жаль, помилка - данний нікнейм вже зайнят, спробуйте інший.";
            return;
        }

        if ($this->loginModel->isNicknameTaken($nickname, 0)) {
            $this->nicknameStatus = 'offline';
            return;
        }

        $this->nicknameStatus = 'new';
    }

    /**
     * @throws Exception
     */
    private function registerUser(string $login): void
    {
        $this->loginModel->registerLogin($login);
    }
}
