<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\v1\Example;
use App\Helper\Token;

class ExampleController extends Controller
{

    public function test(Request $request)
    {
        return 'hello';
    }

    /**
    * POST api.kami.example.token
    */
    public function token()
    {
        $token = Token::encode(['uid' => 10]);
        return $this->body(['token' => $token]);
    }

    /**
    * POST api.kami.example.list
    */
    public function index()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        extract($this->validated);

        $model = Example::where('id', '>', 0);
        $total = $model->count();
        $data = $model
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)->toArray();

        return $this->body(['examples' => $data['data'], 'paged' => $this->formatPaged($page, $per_page, $total)]);
    }    

    /**
    * POST api.kami.example.get
    */
    public function view()
    {
        $rules = [
            'id' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        extract($this->validated);

        if ($model = Example::find($id)) {
            return $this->body(['example' => $model]);
        }
        return $this->error(self::NOT_FOUND);
    }

    /**
    * POST api.kami.example.add
    */
    public function create()
    {
        $rules = [
            'foo' => 'required|string',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        if (Example::create($this->validated)) {
            return $this->body([]);
        }
        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
    * POST api.kami.example.delete
    */
    public function delete()
    {
        $rules = [
            'id' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        extract($this->validated);

        if ($model = Example::find($id)) {
            if ($model->delete()) {
                return $this->body([]);
            }
        }
        return $this->error(self::NOT_FOUND);
    }

    /**
    * POST api.kami.example.update
    */
    public function update()
    {
        $rules = [
            'id' => 'required|integer|min:1',
            'foo' => 'required|string',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        extract($this->validated);

        if ($model = Example::find($id)) {
            if ($model->update($this->validated))
            {
                return $this->body([]);
            }
        }
        return $this->error(self::NOT_FOUND);
    }
}
