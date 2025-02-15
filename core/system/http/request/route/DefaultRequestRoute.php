<?php

namespace fmr\system\http\request\route;


/**
 * Dynamic route implementation to resolve HTTP requests, handling controllers using a distinct pattern.
 */
class DefaultRequestRoute implements RequestRouteInterface
{
    /**
     * schema for outgoing links
     * @var array[]
     */
    protected array $buildSchema = [];

    /**
     * pattern for incoming requests
     * @var string
     */
    protected string $pattern = '';

    /**
     * list of required components
     * @var string[]
     */
    protected array $requireComponents = [];

    /**
     * parsed request data
     * @var mixed[]
     */
    protected array $routeData = [];

    /**
     * DynamicRequestRoute constructor.
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Sets default routing information.
     */
    protected function init() {
        $this->setPattern('~
			/?
			(?:
				(?P<controller>
					(?:
						[a-z][a-z0-9]+
						(?:-[a-z][a-z0-9]+)*
					)+
				)
				(?:/|$)
				(?:
					(?P<id>\d+)
					(?:
						-
						(?P<title>[^/]+)
					)?
				)?
			)?
		~x');
        $this->setBuildSchema('/{controller}/{id}-{title}/');
    }

    /**
     * Sets the build schema used to build outgoing links.
     *
     * @param string $buildSchema
     */
    public function setBuildSchema(string $buildSchema) {
        $this->buildSchema = [];

        $buildSchema = \ltrim($buildSchema, '/');
        $components = \preg_split(
            '~({(?:[a-z]+)})~',
            $buildSchema,
            -1,
            \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
        );

        foreach ($components as $component) {
            $type = 'component';
            if (\preg_match('~{([a-z]+)}~', $component, $matches)) {
                $component = $matches[1];
            } else {
                $type = 'separator';
            }

            $this->buildSchema[] = [
                'type' => $type,
                'value' => $component,
            ];
        }
    }

    /**
     * Sets the route pattern used to evaluate an incoming request.
     *
     * @param string $pattern
     */
    public function setPattern(string $pattern) {
        $this->pattern = $pattern;
    }

    /**
     * Sets the list of required components.
     *
     * @param string[] $requiredComponents
     */
    public function setRequiredComponents(array $requiredComponents) {
        $this->requireComponents = $requiredComponents;
    }

    /**
     * @inheritDoc
     */
    public function buildLink(array $components): string {
        $application = $components['application'] ?? null;

        // drop application component to avoid being appended as query string
        unset($components['application']);

        // handle default values for controller
        $useBuildSchema = true;
        if (\count($components) == 1 && isset($components['controller'])) {
            if (
                ControllerMap::getInstance()->isDefaultController($components['controller'])
            ) {
                // drops controller from route
                $useBuildSchema = false;

                // unset the controller, since it would otherwise be added with http_build_query()
                unset($components['controller']);
            }
        }

        return $this->buildRoute($components, $useBuildSchema);
    }

    /**
     * Builds the actual link, the parameter $useBuildSchema can be set to false for
     * empty routes, e.g. for the default page.
     *
     * @param string[] $components
     * @param string $application
     * @param bool $useBuildSchema
     * @return  string
     */
    protected function buildRoute(array $components, bool $useBuildSchema): string {
        $link = '';

        if ($useBuildSchema) {
            $lastSeparator = null;
            $skipToLastSeparator = false;
            foreach ($this->buildSchema as $component) {
                $value = $component['value'];

                if ($component['type'] === 'separator') {
                    $lastSeparator = $value;
                } elseif ($skipToLastSeparator === false) {
                    // routes are build from left-to-right
                    if (empty($components[$value])) {
                        $skipToLastSeparator = true;

                        // drop empty components to avoid them being appended as query string argument
                        unset($components[$value]);

                        continue;
                    }

                    if ($lastSeparator !== null) {
                        $link .= $lastSeparator;
                        $lastSeparator = null;
                    }

                    // handle controller names
                    if ($value === 'controller') {
                        $components[$value] = ControllerMap::getInstance()->lookup(
                            $components[$value]
                        );
                    }

                    $link .= $components[$value];
                    unset($components[$value]);
                }
            }

            if ($link !== '' && $lastSeparator !== null) {
                $link .= $lastSeparator;
            }
        }

        if ($components !== []) {
            $link .= \str_contains($link, '?') ? '&' : '?';
            $link .= \http_build_query($components, '', '&');
        }

        return $link;
    }

    /**
     * @inheritDoc
     */
    public function canHandle(array $components): bool {
        if (!empty($this->requireComponents)) {
            foreach ($this->requireComponents as $component => $pattern) {
                if (empty($components[$component])) {
                    return false;
                }

                if ($pattern && !\preg_match($pattern, (string)$components[$component])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getRouteData(): array {
        return $this->routeData;
    }


    /**
     * @inheritDoc
     */
    public function matches(string $requestURL): bool {

        if (\preg_match($this->pattern, $requestURL, $matches)) {

            foreach ($matches as $key => $value) {
                if (!\is_numeric($key)) {
                    $this->routeData[$key] = $value;
                }
            }

            $this->routeData['isDefaultController'] = (!isset($this->routeData['controller']));
            if ($this->routeData['isDefaultController'] && empty($requestURL)) {
                // pretend that this controller has been renamed
                $this->routeData['isRenamedController'] = true;
            }

            return true;
        }

        return false;
    }
}