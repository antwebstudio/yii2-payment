<?php

namespace ant\payment\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\payment\models\Invoice;
use common\modules\contact\models\Contact;

/**
 * InvoiceSearch represents the model behind the search form about `common\modules\payment\models\Invoice`.
 */
class InvoiceSearch extends Invoice
{
    public $userId;

    /**
     * @inheritdoc
     */
    public $contactName;

    public function rules()
    {
        return [
            [['id',], 'integer'],
            [['userId', 'formatted_id', 'due_date', 'issue_date', 'remark', 'created_at', 'updated_at','issue_by', 'issue_to', 'contactName', 'status'], 'safe'],
            [['total_amount', 'discount_amount', 'service_charges_amount', 'tax_amount', 'paid_amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Invoice::find()
        // ->select(new \yii\db\Expression('*, if(issue_to = 0, (SELECT concat(c.firstname , " " , c.lastname) FROM em_order o left join em_contact c on o.billed_to = c.id WHERE o.invoice_id = em_payment_invoice.id  ), contact.contact_name ) AS `issue_persona`'))

        // ->joinWith('contact contact')
        /*->joinWith('order order')
        ->with('order')
        ->join('left join',Contact::tableName(), 'if('.Invoice::tableName().'.issue_to  = 0 ,
            order.billed_to = '.Contact::tableName() . '.id, 
            '.Invoice::tableName().'.issue_to = '.Contact::tableName().'.id)' )*/
        ;

        if (Yii::$app->moduleManager->isModuleEnabled('order')) {
            $query->joinWith('order order');
        }
        $query->joinWith(['billedTo' => function($q) {
            $q->alias('billedTo')->joinWith('user billedToUser');
        }]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }        
        $dataProvider->sort->attributes['contactName'] = [
            'asc' => [
            Contact::tableName() . '.contact_name' => SORT_ASC, 
            Contact::tableName() . '.firstname' => SORT_ASC, 
            Contact::tableName() . '.lastname' => SORT_ASC
            ],
            'desc' => [
            Contact::tableName() . '.contact_name' => SORT_DESC, 
            Contact::tableName() . '.firstname' => SORT_DESC, 
            Contact::tableName() . '.lastname' => SORT_ASC
            ],
        ];

        if (isset($this->contactName) && strlen(trim($this->contactName)))
        {
            $query
                ->andFilterWhere(['or',
                    ['like', Contact::tableName() . '.firstname', $this->contactName],
                    ['like', Contact::tableName() . '.contact_name', $this->issue_to],
                    ['like', Contact::tableName() . '.lastname', $this->contactName],
                ])
                ->orderBy([Contact::tableName() . '.firstname' => SORT_ASC]);

        }

        if (isset($this->status) && strlen(trim($this->status))) {
            if ($this->status == Invoice::STATUS_PAID) {
                $this->status = ['1','2'];
            }
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'service_charges_amount' => $this->service_charges_amount,
            'tax_amount' => $this->tax_amount,
            'paid_amount' => $this->paid_amount,
            'issue_by' => $this->issue_by,
            'due_date' => $this->due_date,
            'issue_date' => $this->issue_date,
            // 'status' => $this->status,
            'updated_at' => $this->updated_at,
        ]);

        $query
            ->andFilterWhere(['OR', ['issue_to' => $this->userId], ['billedToUser.id' => $this->userId]])
            ->andFilterWhere(['like', Invoice::tableName() . '.formatted_id', $this->formatted_id])
            ->andFilterWhere(['like', Invoice::tableName() . '.created_at' , $this->created_at])
            //->andFilterWhere(['like', Contact::tableName() . '.contact_name', $this->contactName])
            ->andFilterWhere([Invoice::tableName() . '.status' => $this->status])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}