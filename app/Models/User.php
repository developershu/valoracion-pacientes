<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Especifica la conexión de base de datos para este modelo
     */
    protected $connection = 'mysql_real';

    /**
     * Especifica la tabla a usar (tabla del sistema hospitalario)
     */
    protected $table = 'usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'personal_id',
        'created_by',
        'modified_by',
        'blocked_account',
        'borrado_logico',
        'cambiar_password',
        'log_attempt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'codigo_ad_hoc',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
            'blocked_account' => 'boolean',
            'borrado_logico' => 'boolean',
            'cambiar_password' => 'boolean',
        ];
    }

    /**
     * Obtener el nombre para mostrar
     */
    public function getNameAttribute()
    {
        return $this->username;
    }

    /**
     * Obtener email (no existe en esta tabla, usar username)
     */
    public function getEmailAttribute()
    {
        return $this->username . '@hospital.uncu.edu.ar';
    }

    /**
     * Override del método para verificar si el usuario está activo
     */
    public function isActive()
    {
        return !$this->blocked_account && !$this->borrado_logico && is_null($this->deleted_at);
    }
}
