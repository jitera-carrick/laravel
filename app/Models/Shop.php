
<?php

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'name',
        'address',
        // Other existing fillable attributes...
    ];

    public function findById($id)
    {
        return $this->find($id);
    }

    public function updateShopInfo($name, $address)
    {
        $this->name = $name;
        $this->address = $address;
        return $this->save();
    }

    // Other existing methods and relationships...
}
