<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Blade::directive('interact', function (mixed $expression): string {
            $directive = array_map('trim', preg_split('/,(?![^(]*[)])/', $expression));
            $name      = array_shift($directive);
            $arguments = array_shift($directive) ?? '';

            $cleanName = 'column_' . str_replace('.', '_', trim($name, "'\""));

            return "<?php \$__env->slot('{$cleanName}', function({$arguments}, \$loop = null) use (\$__env, \$__blaze) { ?>";
        });

        Blade::directive('endinteract', fn (): string => '<?php }); ?>');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
