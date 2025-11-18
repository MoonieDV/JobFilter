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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'email',
        'role',
        'password',
        'job_title',
        'phone',
        'dob',
        'bio',
        'resume_path',
        'company_name',
        'company_reg_number',
        'company_address',
        'company_phone',
        'company_linkedin',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dob' => 'date',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function postedJobs()
    {
        return $this->hasMany(Job::class, 'posted_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }

    public function receivedApplications()
    {
        return $this->hasMany(Application::class, 'employer_id');
    }

    public function skills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function alerts()
    {
        return $this->hasMany(Notification::class);
    }
}
