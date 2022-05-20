<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->comment('ユーザID');
            $table->integer('facility_id')->default(0)->comment('施設ID');
            $table->string('name')->default('')->comment('ユーザ名');
            $table->string('password')->comment('パスワード');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->boolean('is_adoption_mail')->default(false)->comment('採用承認メール送付フラグ');
            $table->boolean('is_claim_mail')->default(false)->comment('請求登録メール送付フラグ');
            $table->boolean('is_login_hold')->default(false)->comment('ログイン状態保持フラグ');
            $table->boolean('is_google_account')->default(false)->comment('Googleアカウント連携フラグ');
            $table->integer('creater')->default(0)->comment('作成者ユーザID');
            $table->integer('updater')->default(0)->comment('更新者ユーザID');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable()->comment('最終ログインID');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
