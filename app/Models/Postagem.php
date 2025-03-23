<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postagem extends Model
{
    use HasFactory;

    //Adicionando texto a lista de campos prenchiveis
    protected $table = 'postagens';
    
    protected $fillable = [
        'texto',
        'rede_social',
        'sentimento',
    ];

}
