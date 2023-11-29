<?php

namespace App;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFields\EnumModel;
use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultiselectCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

class CommandHandler
{
    private AmoCRMApiClient $client;

    public function __construct()
    {
        $this->client = getClient();

        $tokenParams = json_decode(file_get_contents('tmp/code.txt'), true);

        $this->client->setAccessToken(new AccessToken($tokenParams));
    }

    /**
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     */
    public function addLeads($count): void
    {
        $i = 0;

        while ($i < $count) {
            $contact = (new ContactModel())
                ->setName("Имя контакта $i");
            $company = (new CompanyModel())
                ->setName("Компания $i");

            $leads[] = (new LeadModel())
                ->setName("Сделка $i")
                ->setContacts(ContactsCollection::make([$contact]))
                ->setCompany($company);

            $i++;

            if (count($leads) == 50 || $i == $count) {
                $this->client->leads()->addComplex(LeadsCollection::make($leads));
                $leads = [];
            }
        }
    }

    public function addMultiList(): void
    {
        $field = (new MultiselectCustomFieldModel())
            ->setName('Кастмное поле из АПИ')
            ->setEnums(
                (new CustomFieldEnumsCollection())
                    ->add(
                        (new EnumModel())
                            ->setValue('Значение 1')
                            ->setCode('first')
                            ->setSort(10)
                    )
                    ->add(
                        (new EnumModel())
                            ->setValue('Значение 2')
                            ->setCode('second')
                            ->setSort(20)
                    )
                    ->add(
                        (new EnumModel())
                            ->setValue('Значение 3')
                            ->setCode('third')
                            ->setSort(30)
                    )
            );

        try {
            /** @var MultiselectCustomFieldModel $field */
            $field = $this->client->customFields('leads')->addOne($field);
            $this->updateAllLeads($field);
        } catch (AmoCRMMissedTokenException|AmoCRMApiException $e) {
            die((string)$e);
        }
    }

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     */
    private function updateAllLeads(MultiselectCustomFieldModel $field): void
    {
        $leads = $this->client->leads()->get();

        $values = (new CustomFieldsValuesCollection());

        $multiselectCustomFieldValueCollection = new MultiselectCustomFieldValueCollection();

        foreach ($field->getEnums() as $enum) {
            $multiselectCustomFieldValueCollection->add(
                (new MultiselectCustomFieldValueModel())->setEnumId($enum->getId())
            );
        }

        $values->add(
            (new MultiselectCustomFieldValuesModel())
                ->setFieldId($field->getId())
                ->setValues($multiselectCustomFieldValueCollection)
        );

        $existsNextPage = true;

        while ($existsNextPage) {
            /** @var LeadModel $lead */
            foreach ($leads as $lead) {
                $lead->setCustomFieldsValues($values);
            }

            $this->client->leads()->update($leads);

            try {
                $leads = $this->client->leads()->nextPage($leads);
            } catch (Exception) {
                $existsNextPage = false;
            }
        }
    }
}