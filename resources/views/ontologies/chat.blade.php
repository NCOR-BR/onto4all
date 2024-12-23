<div class="box-body"></div>
@foreach ($mesagens as $message)

    <div class="direct-chat-messages">

        @if ($message->user_id != Auth::user()->id)

            <div class="direct-chat-msg">
                <div class="direct-chat-info clearfix">
                    <span class="direct-chat-name pull-left">{{ $message->user->name }}</span>
                    <span
                        class="direct-chat-timestamp pull-right">{{ date('d/m/Y h:m:s', strtotime($message->created_at)) }}</span>
                </div>
                <img class="direct-chat-img" src="{{ file_exists(asset('storage/img/profile/' . $message->user->avatar_url)) ? asset('storage/img/profile/' . $message->user->avatar_url) : asset("css/images/profile_default.png") }}" alt="Imagem de perfil">
                <div class="direct-chat-text">
                    {{ $message->message }}
                </div>
            </div>

        @else

            <div class="direct-chat-msg right">
                <div class="direct-chat-info clearfix">
                    <span class="direct-chat-name pull-right">{{ $message->user->name }}</span>
                    <span
                        class="direct-chat-timestamp pull-left">{{ date('d/m/Y h:m:s', strtotime($message->created_at)) }}</span>
                </div>
                <img class="direct-chat-img" src="{{ file_exists(asset('storage/img/profile/' . $message->user->avatar_url)) ? asset('storage/img/profile/' . $message->user->avatar_url) : asset("css/images/profile_default.png") }}" alt="Imagem de perfil">
                <div class="direct-chat-text">
                    {{ $message->message }}
                </div>
            </div>

        @endif

    </div>

@endforeach
