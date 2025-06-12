<h2>Cadastro</h2>
@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <input type="text" name="name" placeholder="Nome" value="{{ old('name') }}" required>
    <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required>
    <input type="password" name="password" placeholder="Senha" required>
    <input type="password" name="password_confirmation" placeholder="Confirme a senha" required>
    <button type="submit">Cadastrar</button>
</form>

<a href="{{ route('login') }}">Já tem conta? Entrar</a>
