<?php
namespace Sandstorm\UserManagement\Domain\Validator;

use Sandstorm\UserManagement\Domain\Model\RegistrationFlow;
use Sandstorm\UserManagement\Domain\Service\RegistrationFlowValidationServiceInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Flow\Validation\Error;

/**
 * Validator for ensuring uniqueness of users, ensuring no new registration flows for existing users can be created.
 */
class RegistrationFlowValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator
{

    /**
     * @var AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @param RegistrationFlow $value The value that should be validated
     * @return void
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     */
    protected function isValid($value)
    {

        $existingAccount = $this->accountRepository->findOneByAccountIdentifier($value->getEmail());

        if ($existingAccount) {
            $this->result->forProperty('email')->addError(new Error('Die Email-Adresse %s wird bereits verwendet!', 1336499566, array($value->getEmail())));
        }

        // If a custom validation service is registered, call its validate method to allow custom validations during registration
        if($this->objectManager->isRegistered(RegistrationFlowValidationServiceInterface::class)){
            $instance = $this->objectManager->get(RegistrationFlowValidationServiceInterface::class);
            $instance->validateRegistrationFlow($value, $this);
        }
    }

    /**
     * The custom validation service might need to access the result directly, so it is exposed here
     *
     * @return \TYPO3\Flow\Error\Result
     */
    public function getResult(){
        return $this->result;
    }
}
