<?php

namespace Ronald2Wing\LaravelGa4\Tests\Traits;

/**
 * Trait for creating fake Livewire classes in tests.
 *
 * This trait provides methods to simulate Livewire being installed
 * in the application for testing purposes.
 */
trait CreatesFakeLivewireClass
{
    /**
     * Create a fake Livewire class to simulate Livewire being installed.
     *
     * This method creates a dummy Livewire\Livewire class using eval()
     * which allows the Ga4Service::isLivewireInstalled() method to return true.
     */
    protected function createFakeLivewireClass(): void
    {
        if (! class_exists('Livewire\Livewire')) {
            eval('namespace Livewire; class Livewire {}');
        }
    }

    /**
     * Simulate Livewire being installed in the application.
     *
     * This is an alias for createFakeLivewireClass() with a more descriptive name.
     */
    protected function simulateLivewireInstalled(): void
    {
        $this->createFakeLivewireClass();
    }

    /**
     * Simulate Livewire NOT being installed in the application.
     *
     * This method ensures no Livewire class exists (though in practice,
     * we can't remove a class once it's defined, so this is more for
     * documentation and test clarity).
     */
    protected function simulateLivewireNotInstalled(): void
    {
        // Note: We can't actually remove a class once it's defined in PHP
        // This method is for test clarity and documentation
    }
}
