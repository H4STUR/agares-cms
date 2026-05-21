<x-app-layout>


<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">System Information</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <tbody>

                        {{-- Project --}}
                        <tr>
                            <th width="250">Project Name</th>
                            <td>{{ config('app.name') }}</td>
                        </tr>

                        <tr>
                            <th>Project Version</th>
                            <td>{{ config('app.version', '1.0.0') }}</td>
                        </tr>

                        {{-- Laravel --}}
                        <tr>
                            <th>Laravel Version</th>
                            <td>{{ app()->version() }}</td>
                        </tr>

                        {{-- PHP --}}
                        <tr>
                            <th>PHP Version</th>
                            <td>{{ phpversion() }}</td>
                        </tr>

                        {{-- Environment --}}
                        <tr>
                            <th>Environment</th>
                            <td>
                                <span class="badge bg-{{ app()->environment('production') ? 'success' : 'warning' }}">
                                    {{ app()->environment() }}
                                </span>
                            </td>
                        </tr>

                        {{-- Debug --}}
                        <tr>
                            <th>Debug Mode</th>
                            <td>
                                @if(config('app.debug'))
                                    <span class="badge bg-danger">ON</span>
                                @else
                                    <span class="badge bg-success">OFF</span>
                                @endif
                            </td>
                        </tr>

                        {{-- URL --}}
                        <tr>
                            <th>App URL</th>
                            <td>{{ config('app.url') }}</td>
                        </tr>

                        {{-- Timezone --}}
                        <tr>
                            <th>Timezone</th>
                            <td>{{ config('app.timezone') }}</td>
                        </tr>

                        {{-- Database --}}
                        <tr>
                            <th>Database</th>
                            <td>{{ config('database.default') }}</td>
                        </tr>

                        <tr>
                            <th>DB Host</th>
                            <td>{{ config('database.connections.' . config('database.default') . '.host') }}</td>
                        </tr>

                        {{-- Server --}}
                        <tr>
                            <th>Server Software</th>
                            <td>{{ $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Server IP</th>
                            <td>{{ request()->server('SERVER_ADDR') }}</td>
                        </tr>

                        {{-- Memory --}}
                        <tr>
                            <th>PHP Memory Limit</th>
                            <td>{{ ini_get('memory_limit') }}</td>
                        </tr>

                        <tr>
                            <th>Max Upload Size</th>
                            <td>{{ ini_get('upload_max_filesize') }}</td>
                        </tr>

                        <tr>
                            <th>Max POST Size</th>
                            <td>{{ ini_get('post_max_size') }}</td>
                        </tr>

                        {{-- Extensions --}}
                        <tr>
                            <th>Loaded PHP Extensions</th>
                            <td style="font-size: 13px;">
                                {{ implode(', ', get_loaded_extensions()) }}
                            </td>
                        </tr>

                        {{-- Cache --}}
                        <tr>
                            <th>Cache Driver</th>
                            <td>{{ config('cache.default') }}</td>
                        </tr>

                        {{-- Queue --}}
                        <tr>
                            <th>Queue Driver</th>
                            <td>{{ config('queue.default') }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


</x-app-layout>