<h2>Login</h2>

@if(session('success'))
    <div style="color: green;">
        {{ session('success') }}
    </div>
@endif


<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="password" placeholder="Senha" required>
    <button type="submit">Entrar</button>
</form>

<a href="{{ route('register') }}">Criar nova conta</a>
