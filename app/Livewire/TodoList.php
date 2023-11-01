<?php

namespace App\Livewire;

use App\Models\Todo;
use Exception;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{

    use WithPagination;

    #[Rule('required|unique:todos|min:3|max:50')]
    public $name;

    public $search;

    public $EditingTodoID;


    #[Rule('required|min:3|max:50')]
    public $EditingName;

    public function create() {
        //validate
        $validated = $this->validateOnly('name');
        //create the todo
        Todo::create($validated);
        //clear the input
        $this->reset('name');
        //send flash message
        session()->flash('success', 'Created.');
        $this->resetPage();
    }

    public function delete($id) {
        try {
            Todo::findOrfail($id)->delete();
        } catch(Exception $e) {
            session()->flash('error', 'Failed to Delete todo');
            return;
        }
    }

    public function toggle($id) {
        $todo = Todo::findOrfail($id);
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($id) {
        $this->EditingTodoID = $id;
        $this->EditingName = Todo::find($id)->name;
    }

    public function cancelEdit() {
        $this->reset('EditingTodoID', 'EditingName');
    }
    public function update() {
        $this->validateOnly('EditingName');
        Todo::findOrfail($this->EditingTodoID)->updateOrfail([
            'name' => $this->EditingName
        ]);

        $this->cancelEdit();
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(5),
        ]);
    }
}
