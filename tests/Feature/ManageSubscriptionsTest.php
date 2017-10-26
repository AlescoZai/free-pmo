<?php

namespace Tests\Feature;

use App\Entities\Projects\Project;
use App\Entities\Subscriptions\Subscription;
use Tests\TestCase;

class ManageSubscriptionsTest extends TestCase
{
    /** @test */
    public function admin_can_entry_subscription()
    {
        $user = $this->adminUserSigningIn();
        $vendor = $this->createUser('vendor');
        $project = factory(Project::class)->create();
        $customer = $this->createUser('customer');

        $this->visit(route('subscriptions.index'));
        $this->click(trans('subscription.create'));

        // Fill Form
        $this->type('www.domain.com', 'domain_name');
        $this->type(100000, 'domain_price');
        $this->type('EPPCODE', 'epp_code');
        $this->type('3GB', 'hosting_capacity');
        $this->type(500000, 'hosting_price');
        $this->type('2015-05-02', 'start_date');
        $this->type('2016-05-02', 'due_date');
        $this->select($project->id, 'project_id');
        $this->select($customer->id, 'customer_id');
        $this->select($vendor->id, 'vendor_id');
        $this->type('', 'remark');
        $this->press(trans('subscription.create'));

        $this->seePageIs(route('subscriptions.index'));
        $this->see(trans('subscription.created'));

        $this->seeInDatabase('subscriptions', [
            'project_id' => $project->id,
            'domain_price' => 100000,
            'epp_code' => 'EPPCODE',
            'status_id' => 1,
            'start_date' => '2015-05-02',
            'due_date' => '2016-05-02',
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    /** @test */
    public function admin_can_edit_subscription_data()
    {
        $user = $this->adminUserSigningIn();
        $vendor = $this->createUser('vendor');
        $eppCode = str_random(10);
        $project = factory(Project::class)->create();
        $customer = $this->createUser('customer');

        $subscription = factory(Subscription::class)->create(['customer_id' => $customer->id, 'project_id' => $project->id]);

        $this->visit(route('subscriptions.edit', $subscription->id));
        $this->seePageIs(route('subscriptions.edit', $subscription->id));

        // Fill Form
        $this->type($eppCode, 'epp_code');
        $this->type('4GB', 'hosting_capacity');
        $this->type(500000, 'hosting_price');
        $this->type('2015-05-02', 'start_date');
        $this->type('2016-05-02', 'due_date');
        $this->select($project->id, 'project_id');
        $this->select($customer->id, 'customer_id');
        $this->select($vendor->id, 'vendor_id');
        $this->select(1, 'status_id');
        $this->press(trans('subscription.update'));

        $this->seePageIs(route('subscriptions.edit', $subscription->id));
        $this->see(trans('subscription.updated'));
        $this->seeInDatabase('subscriptions', [
            'epp_code' => $eppCode,
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'status_id' => 1,
            'hosting_capacity' => '4GB',
            'hosting_price' => '500000',
            'start_date' => '2015-05-02',
            'due_date' => '2016-05-02',
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    /** @test */
    public function admin_can_delete_a_subscription()
    {
        $user = $this->adminUserSigningIn();

        $subscription = factory(Subscription::class)->create();

        $this->visit(route('subscriptions.edit', $subscription->id));
        $this->click(trans('subscription.delete'));
        $this->press(trans('app.delete_confirm_button'));
        $this->seePageIs(route('subscriptions.index'));
        $this->see(trans('subscription.deleted'));

        $this->dontSeeInDatabase('subscriptions', ['id' => $subscription->id]);
    }

    /** @test */
    public function admin_can_see_a_subscription()
    {
        $user = $this->adminUserSigningIn();

        $subscription = factory(Subscription::class)->create();

        $this->visit(route('subscriptions.show', $subscription->id));

        $this->see($subscription->domain_name);
        $this->see(formatRp($subscription->domain_price));
        $this->see($subscription->hosting_capacity);
        $this->see(formatRp($subscription->hosting_price));
        $this->see(dateId($subscription->start_date));
        $this->see(dateId($subscription->due_date));
    }
}
