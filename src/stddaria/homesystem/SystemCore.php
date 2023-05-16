<?php

namespace stddaria\homesystem;

use stddaria\homesystem\bootstrap\Loader;
use stddaria\homesystem\command\CommandRegistry;
use stddaria\homesystem\config\LoadedConfig;
use stddaria\homesystem\event\handler\MainEventHandler;
use stddaria\homesystem\home\HomeRegistry;

class SystemCore {
    private Loader $mLoader;
    private CommandRegistry $mCommandRegistry;
    private HomeRegistry $mHomeRegistry;
    private MainEventHandler $mEventHandler;
    private LoadedConfig $mConfig;

    public function __construct( Loader $pLoader ) {
        $this->mLoader = $pLoader;
        $this->mCommandRegistry = new CommandRegistry( $this );
        $this->mHomeRegistry = new HomeRegistry( $this );
        $this->mEventHandler = new MainEventHandler( $this );
        $this->mConfig = new LoadedConfig($pLoader->getConfig());
    }

    public function init(): void {
        $this->mConfig->load();

        $this->mCommandRegistry->setup();
        $this->mHomeRegistry->init();
        $this->mLoader->getServer()->getPluginManager()->registerEvents( $this->mEventHandler, $this->mLoader );
    }

    public function getConfig() : LoadedConfig {
        return $this->mConfig;
    }

    public function getCommandRegistry(): CommandRegistry {
        return $this->mCommandRegistry;
    }

    public function getHomeRegistry(): HomeRegistry {
        return $this->mHomeRegistry;
    }

    public function getLoader(): Loader {
        return $this->mLoader;
    }
}