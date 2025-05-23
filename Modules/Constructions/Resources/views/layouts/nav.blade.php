<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action([\Modules\Constructions\Http\Controllers\ConstructionsController::class, 'index'])}}"><i class="fas fa-building"></i> {{__('constructions::lang.constructions')}}</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @if(auth()->user()->can('constructions.view_work_certificate'))
                        <li @if(request()->segment(2) == 'work-certificates') class="active" @endif>
                            <a href="{{action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'index'])}}">
                                @lang('constructions::lang.work_certificates')
                            </a>
                        </li>
                    @endif
                   
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section> 