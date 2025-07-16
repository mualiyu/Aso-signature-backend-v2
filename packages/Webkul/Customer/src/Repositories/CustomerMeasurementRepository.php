<?php

namespace Webkul\Customer\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Customer\Contracts\Measurement;

class CustomerMeasurementRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return Measurement::class;
    }

    /**
     * Create a new customer measurement.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update customer measurement.
     */
    public function update(array $data, $id)
    {
        $measurement = $this->find($id);

        if ($measurement) {
            $measurement->update($data);
        }

        return $measurement;
    }

    /**
     * Delete customer measurement.
     */
    public function delete($id)
    {
        $measurement = $this->find($id);

        if ($measurement) {
            return $measurement->delete();
        }

        return false;
    }

    /**
     * Get measurements by customer ID.
     */
    public function findByCustomerId($customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
    }
}
