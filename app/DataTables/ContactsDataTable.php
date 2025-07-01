<?php

namespace App\DataTables;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ContactsDataTable extends DataTable
{
    protected $isTrashed = false;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addColumn('select', function (Contact $contact) {
                return '<input type="checkbox" class="contact-select-checkbox" data-id="' . $contact->id . '">';
            })
            ->addColumn('action', function (Contact $contact) {
            $buttons = "";
            $showUrl = route('contacts.show', $contact->id);
            if ($contact->trashed()) { 
                $buttons .= "<a href='{$showUrl}' class='inline-flex items-center px-2 py-1 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-1'>View</a>";
                $buttons .= "<button class='inline-flex items-center px-2 py-1 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-1 restore-contact' data-id='{$contact->id}'>Restore</button>";
                $buttons .= " <button class='inline-flex items-center px-2 py-1 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-900 focus:bg-red-900 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2 transition ease-in-out duration-150 force-delete-contact' data-id='{$contact->id}'>Force Delete</button>";
            } else {
                $editUrl = route('contacts.edit', $contact->id);
                

                $buttons .= "<a href='{$showUrl}' class='inline-flex items-center px-2 py-1 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-1'>View</a>";
                $buttons .= "<a href='{$editUrl}' class='inline-flex items-center px-2 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-1'>Edit</a>";
                $buttons .= " <button class='inline-flex items-center px-2 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 delete-contact' data-id='{$contact->id}'>Delete</button>";
            }
            return $buttons;
        })
            ->addColumn('profile_image_thumbnail', function (Contact $contact) {
                
                if ($contact->profile_image && Storage::exists($contact->profile_image)) {
                    
                    return '<img src="' . Storage::url($contact->profile_image) . '" class="w-10 h-10 object-cover rounded-full" alt="Profile Image">';
                }
                return '<span class="text-gray-400">No Image</span>';
            })
            
            ->addColumn('custom_fields_summary', function (Contact $contact) {
                $summary = [];
                foreach ($contact->customFields as $customField) {
                    $summary[] = $customField->definition->field_name . ': ' . $customField->value;
                }
                return implode('<br>', $summary); 
            })
            
            ->filter(function (QueryBuilder $query) {
                if ($this->request()->has('name_filter') && $this->request()->get('name_filter')) {
                    $query->where('name', 'like', '%' . $this->request->get('name_filter') . '%');
                }
                if ($this->request()->has('email_filter') && $this->request()->get('email_filter')) {
                    $query->where('email', 'like', '%' . $this->request->get('email_filter') . '%');
                }
                if ($this->request()->has('gender_filter') && $this->request()->get('gender_filter')) {
                    $query->where('gender', $this->request->get('gender_filter'));
                }
                
                if ($this->request()->has('custom_field_name') && $this->request()->get('custom_field_name')) {
                    $fieldName = $this->request->get('custom_field_name');
                    $fieldValue = $this->request->get('custom_field_value');

                    $query->whereHas('customFields.definition', function ($q) use ($fieldName, $fieldValue) {
                        $q->where('field_name', $fieldName);
                        if ($fieldValue) {
                            $q->where('value', 'like', '%' . $fieldValue . '%');
                        }
                    });
                }
            })
            ->rawColumns(['select', 'action', 'profile_image_thumbnail', 'custom_fields_summary']); 
    }


    public function forTrashed(): self
    {
        $this->isTrashed = true;
        return $this; 
    }

    
    public function query(Contact $model): QueryBuilder
    {

        $query = $model->newQuery()->where('merge_status', 'active')->with('customFields.definition');

         if ($this->isTrashed) {
            return $query->onlyTrashed();
        }

        return $query->whereNull('deleted_at');
    }

    public function html(): \Yajra\DataTables\Html\Builder
    {
        return $this->builder()
            ->setTableId('contacts-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Blfrtip') 
            ->orderBy(1) 
            ->selectStyle('multi') 
            ->buttons(
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            );
    }


    protected function getColumns(): array
    {
        return [
            Column::computed('select') 
                  ->title('<input type="checkbox" id="select-all-contacts">') 
                  ->exportable(false)
                  ->printable(false)
                  ->orderable(false)
                  ->searchable(false)
                  ->width(20)
                  ->addClass('text-center'),
            Column::make('id'),
            Column::make('name'),
            Column::make('email'),
            Column::make('phone'),
            Column::make('gender'),
            Column::make('profile_image_thumbnail')->title('Image')->searchable(false)->orderable(false),
            Column::computed('custom_fields_summary')->title('Custom Fields')->exportable(false)->printable(false)->orderable(false), 
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(150)
                  ->addClass('text-center'),
        ];
    }

    
    protected function filename(): string
    {
        return 'Contacts_' . date('YmdHis');
    }
}