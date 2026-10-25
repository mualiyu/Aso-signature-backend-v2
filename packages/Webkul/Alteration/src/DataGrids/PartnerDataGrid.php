<?php

namespace Webkul\Alteration\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class PartnerDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('alteration_partners')
            ->select(
                'alteration_partners.id',
                'alteration_partners.name',
                'alteration_partners.city',
                'alteration_partners.country',
                'alteration_partners.outreach_status',
                'alteration_partners.vetting_status',
                'alteration_partners.is_active',
                'alteration_partners.sourced_by',
                DB::raw('(SELECT COUNT(*) FROM '.DB::getTablePrefix().'alteration_partner_prices WHERE '.DB::getTablePrefix().'alteration_partner_prices.alteration_partner_id = '.DB::getTablePrefix().'alteration_partners.id) as prices_count')
            );
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('alteration::app.admin.partners.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('alteration::app.admin.partners.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'city',
            'label'      => trans('alteration::app.admin.partners.datagrid.city'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'country',
            'label'      => trans('alteration::app.admin.partners.datagrid.country'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'outreach_status',
            'label'              => trans('alteration::app.admin.partners.datagrid.outreach'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->options('outreach_statuses', 'outreach-statuses'),
            'sortable'           => true,
            'closure'            => function ($row) {
                $class = match ($row->outreach_status) {
                    'positive'              => 'label-active',
                    'negative', 'canceled'  => 'label-canceled',
                    'contacted_no_response' => 'label-pending',
                    default                 => 'label-closed',
                };

                return '<p class="'.$class.'">'.trans('alteration::app.admin.outreach-statuses.'.$row->outreach_status).'</p>';
            },
        ]);

        $this->addColumn([
            'index'              => 'vetting_status',
            'label'              => trans('alteration::app.admin.partners.datagrid.vetting'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->options('vetting_statuses', 'vetting-statuses'),
            'sortable'           => true,
            'closure'            => function ($row) {
                $class = match ($row->vetting_status) {
                    'vetted'   => 'label-active',
                    'rejected' => 'label-canceled',
                    default    => 'label-pending',
                };

                return '<p class="'.$class.'">'.trans('alteration::app.admin.vetting-statuses.'.$row->vetting_status).'</p>';
            },
        ]);

        $this->addColumn([
            'index'              => 'is_active',
            'label'              => trans('alteration::app.admin.partners.datagrid.live'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('alteration::app.admin.partners.datagrid.yes'), 'value' => 1],
                ['label' => trans('alteration::app.admin.partners.datagrid.no'), 'value' => 0],
            ],
            'sortable'           => true,
            'closure'            => fn ($row) => $row->is_active
                ? trans('alteration::app.admin.partners.datagrid.yes')
                : trans('alteration::app.admin.partners.datagrid.no'),
        ]);

        $this->addColumn([
            'index'    => 'prices_count',
            'label'    => trans('alteration::app.admin.partners.datagrid.prices'),
            'type'     => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'sourced_by',
            'label'      => trans('alteration::app.admin.partners.datagrid.sourced-by'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('alterations.partners.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('alteration::app.admin.partners.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.alterations.partners.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('alterations.partners.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('alteration::app.admin.partners.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.alterations.partners.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('alterations.partners.edit')) {
            $this->addMassAction([
                'title'   => trans('alteration::app.admin.partners.datagrid.update-outreach'),
                'method'  => 'POST',
                'url'     => route('admin.alterations.partners.mass_update'),
                'options' => $this->options('outreach_statuses', 'outreach-statuses'),
            ]);
        }
    }

    /**
     * Dropdown options from a config list, labelled through the lang file.
     */
    protected function options(string $configKey, string $langKey): array
    {
        return collect(config('alteration.'.$configKey))
            ->map(fn ($value) => [
                'label' => trans('alteration::app.admin.'.$langKey.'.'.$value),
                'value' => $value,
            ])
            ->all();
    }
}
