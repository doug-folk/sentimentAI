import { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  BarChart,
  Bar
} from 'recharts';
import {
  TrendingUp,
  TrendingDown,
  Smile,
  Frown,
  Meh,
  MessageSquare,
  Filter,
  LogOut,
  Plus,
  ListFilter,
  MessageCircleQuestion,
  Loader2
} from 'lucide-react';
import { addDays, format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { Link } from 'react-router-dom';

import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter
} from '../components/ui/dialog';

const COLORS = {
  POSITIVO: '#10B981',
  NEGATIVO: '#EF4444',
  NEUTRAL: '#6B7280'
};

const StatCard = ({ title, value, icon: Icon, trend, color = "text-gray-600" }) => (
  <Card>
    <CardContent className="p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className={`text-2xl font-bold ${color}`}>{value}</p>
          {trend !== undefined && (
            <p className="text-xs text-gray-500 flex items-center mt-1">
              {trend > 0 ? (
                <TrendingUp className="h-3 w-3 mr-1 text-green-500" />
              ) : (
                <TrendingDown className="h-3 w-3 mr-1 text-red-500" />
              )}
              {Math.abs(trend)}%
            </p>
          )}
        </div>
        <Icon className={`h-8 w-8 ${color}`} />
      </div>
    </CardContent>
  </Card>
);

export default function Dashboard() {
  const { user, logout, token, isAuthenticated } = useAuth();
  const [stats, setStats] = useState(null);
  const [trends, setTrends] = useState([]);
  const [sentimentDistribution, setSentimentDistribution] = useState([]);
  const [socialMediaStats, setSocialMediaStats] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [dateRange, setDateRange] = useState({
    from: addDays(new Date(), -30),
    to: new Date()
  });
  const [selectedPeriod, setSelectedPeriod] = useState('30');

  const [postagensDetalhadas, setPostagensDetalhadas] = useState([]);
  const [loadingPostagens, setLoadingPostagens] = useState(false);
  const [showPostagensModal, setShowPostagensModal] = useState(false);
  const [selectedSentimentForList, setSelectedSentimentForList] = useState(null);

  const API_BASE_URL = 'http://localhost:8000/api';

  console.log('Dashboard Component Renderizado.');

  const handlePeriodChange = (period) => {
    setSelectedPeriod(period);
    const days = parseInt(period);
    const newDateRange = {
      from: addDays(new Date(), -days),
      to: new Date()
    };
    setDateRange(newDateRange);
  };

  const fetchData = async () => {
    console.log('fetchData: Iniciando...');
    console.log('fetchData: isAuthenticated:', isAuthenticated, 'token:', token);

    if (!isAuthenticated || !token) {
      setLoading(false);
      setError('Usuário não autenticado ou token ausente. Não é possível carregar o dashboard.');
      console.warn('fetchData: Abortado - Usuário não autenticado ou token ausente.');
      return;
    }

    setLoading(true);
    setError(null);

    const formattedStartDate = dateRange.from ? format(dateRange.from, 'yyyy-MM-dd') : null;
    const formattedEndDate = dateRange.to ? format(dateRange.to, 'yyyy-MM-dd') : null;

    const queryParams = new URLSearchParams();
    if (formattedStartDate) queryParams.append('start_date', formattedStartDate);
    if (formattedEndDate) queryParams.append('end_date', formattedEndDate);

    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    };

    try {
      console.log('fetchData: Realizando chamadas fetch para a API...');

      const statsResponse = await fetch(`${API_BASE_URL}/dashboard/stats?${queryParams.toString()}`, { headers });
      if (statsResponse.ok) {
        const data = await statsResponse.json();
        setStats(data);
        console.log('fetchData: Stats carregados:', data);
      } else {
        const errorData = await statsResponse.json();
        throw new Error(`Falha ao carregar estatísticas (${statsResponse.status}): ${errorData.message || statsResponse.statusText}`);
      }

      const trendsResponse = await fetch(`${API_BASE_URL}/dashboard/trends?${queryParams.toString()}`, { headers });
      if (trendsResponse.ok) {
        const data = await trendsResponse.json();
        setTrends(data);
        console.log('fetchData: Trends carregados:', data);
      } else {
        const errorData = await trendsResponse.json();
        throw new Error(`Falha ao carregar tendências (${trendsResponse.status}): ${errorData.message || trendsResponse.statusText}`);
      }

      const distributionResponse = await fetch(`${API_BASE_URL}/dashboard/sentiment-distribution?${queryParams.toString()}`, { headers });
      if (distributionResponse.ok) {
        const data = await distributionResponse.json();
        setSentimentDistribution(data);
        console.log('fetchData: Distribuição de sentimento carregada:', data);
      } else {
        const errorData = await distributionResponse.json();
        throw new Error(`Falha ao carregar distribuição (${distributionResponse.status}): ${errorData.message || distributionResponse.statusText}`);
      }

      const socialResponse = await fetch(`${API_BASE_URL}/dashboard/social-media-stats?${queryParams.toString()}`, { headers });
      if (socialResponse.ok) {
        const data = await socialResponse.json();
        setSocialMediaStats(data);
        console.log('fetchData: Stats de redes sociais carregados:', data);
      } else {
        const errorData = await socialResponse.json();
        throw new Error(`Falha ao carregar stats de redes sociais (${socialResponse.status}): ${errorData.message || socialResponse.statusText}`);
      }

    } catch (err) {
      console.error('fetchData: Erro ao buscar dados do dashboard:', err);
      setError(err.message || 'Erro de conexão ao carregar dados do dashboard.');
    } finally {
      setLoading(false);
      console.log('fetchData: Finalizado.');
    }
  };

  const fetchPostagensDetalhadas = async (sentimentFilter = null) => {
    setLoadingPostagens(true);
    setSelectedSentimentForList(sentimentFilter);
    setError(null);

    const formattedStartDate = dateRange.from ? format(dateRange.from, 'yyyy-MM-dd') : null;
    const formattedEndDate = dateRange.to ? format(dateRange.to, 'yyyy-MM-dd') : null;

    const queryParams = new URLSearchParams();
    if (formattedStartDate) queryParams.append('start_date', formattedStartDate);
    if (formattedEndDate) queryParams.append('end_date', formattedEndDate);
    if (sentimentFilter) queryParams.append('sentimento', sentimentFilter);

    try {
      const response = await fetch(`${API_BASE_URL}/postagens?${queryParams.toString()}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        setPostagensDetalhadas(data.data);
        setShowPostagensModal(true);
      } else {
        const errorData = await response.json();
        setError(errorData.message || 'Falha ao carregar postagens detalhadas.');
      }
    } catch (err) {
      console.error('Erro ao buscar postagens detalhadas:', err);
      setError('Erro de conexão ao carregar postagens detalhadas.');
    } finally {
      setLoadingPostagens(false);
    }
  };

  const calculateOverallMood = (statsData) => {
    if (!statsData || statsData.total_postagens === 0) {
      return { mood: 'N/A', icon: MessageCircleQuestion, color: 'text-gray-500', phrase: 'Nenhuma postagem encontrada para analisar.' };
    }

    const { positivos, negativos, neutros } = statsData;
    const total = positivos + negativos + neutros;

    if (total === 0) {
      return { mood: 'N/A', icon: MessageCircleQuestion, color: 'text-gray-500', phrase: 'Nenhuma postagem encontrada para analisar.' };
    }

    let moodText = '';
    let moodIcon = MessageCircleQuestion;
    let moodColor = 'text-gray-500';

    if (positivos > negativos && positivos > neutros) {
      moodText = 'Positivo';
      moodIcon = Smile;
      moodColor = 'text-green-600';
    } else if (negativos > positivos && negativos > neutros) {
      moodText = 'Negativo';
      moodIcon = Frown;
      moodColor = 'text-red-600';
    } else {
      moodText = 'Neutro';
      moodIcon = Meh;
      moodColor = 'text-gray-600';
    }
    
    let phrase = `Seu humor está ${moodText}`;

    return { mood: moodText, icon: moodIcon, color: moodColor, phrase: phrase };
  };

  const overallMood = calculateOverallMood(stats);
  const MoodIcon = overallMood.icon;

  useEffect(() => {
    console.log('useEffect: Disparando fetchData devido a mudança em dependências.');
    fetchData();
  }, [isAuthenticated, token, dateRange]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <p className="text-lg text-gray-700">Carregando dados do dashboard...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 p-4">
        <p className="text-lg text-red-600 mb-4">Ocorreu um erro ao carregar o dashboard:</p>
        <p className="text-md text-red-500 text-center">{error}</p>
        <Button onClick={fetchData} className="mt-4">Tentar Novamente</Button>
      </div>
    );
  }

  const modalPeriodTitle = () => {
    const startFormatted = dateRange.from ? format(dateRange.from, 'dd/MM/yyyy', { locale: ptBR }) : '';
    const endFormatted = dateRange.to ? format(dateRange.to, 'dd/MM/yyyy', { locale: ptBR }) : '';
    if (startFormatted === endFormatted) {
      return `do dia ${startFormatted}`;
    }
    return `de ${startFormatted} a ${endFormatted}`;
  };


  return (
    <>
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <h1 className="text-xl font-semibold text-gray-900">SentimentAI Dashboard</h1>
            </div>
            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-700">Olá, {user?.name || 'Usuário'}</span>
              <Button variant="outline" size="sm" onClick={logout}>
                <LogOut className="h-4 w-4 mr-2" />
                Sair
              </Button>
            </div>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8 flex flex-wrap gap-4 items-center">
          <div className="flex items-center space-x-2">
            <Filter className="h-4 w-4 text-gray-500" />
            <span className="text-sm font-medium text-gray-700">Filtros:</span>
          </div>

          <Select value={selectedPeriod} onValueChange={handlePeriodChange}>
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Período" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="1">Último 1 dia</SelectItem>
              <SelectItem value="7">Últimos 7 dias</SelectItem>
              <SelectItem value="30">Últimos 30 dias</SelectItem>
              <SelectItem value="90">Últimos 3 meses</SelectItem>
              <SelectItem value="365">Último ano</SelectItem>
            </SelectContent>
          </Select>

          <Button onClick={() => fetchPostagensDetalhadas()} disabled={loadingPostagens}>
            {loadingPostagens ? (
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            ) : (
              <ListFilter className="h-4 w-4 mr-2" />
            )}
            Ver Postagens
          </Button>

          <Link to="/new-analysis">
            <Button>
              <Plus className="h-4 w-4 mr-2" />
              Nova Análise
            </Button>
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard
            title="Total de Análises"
            value={stats?.total_postagens ?? 0}
            icon={MessageSquare}
            color="text-blue-600"
          />
          <StatCard
            title="Sentimentos Positivos"
            value={`${stats?.positivos ?? 0} (${(stats?.percentual_positivo ?? 0).toFixed(1)}%)`}
            icon={Smile}
            color="text-green-600"
          />
          <StatCard
            title="Sentimentos Negativos"
            value={`${stats?.negativos ?? 0} (${(stats?.percentual_negativo ?? 0).toFixed(1)}%)`}
            icon={Frown}
            color="text-red-600"
          />
          <StatCard
            title="Sentimentos Neutros"
            value={`${stats?.neutros ?? 0} (${(stats?.percentual_neutro ?? 0).toFixed(1)}%)`}
            icon={Meh}
            color="text-gray-600"
          />
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Humor Geral do Período</p>
                  <p className={`text-xl font-bold ${overallMood.color}`}>{overallMood.phrase}</p>
                </div>
                <MessageCircleQuestion className={`h-8 w-8 ${overallMood.color}`} />
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <Card>
            <CardHeader>
              <CardTitle>Tendências de Sentimentos</CardTitle>
              <CardDescription>
                Evolução dos sentimentos ao longo do tempo
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <LineChart data={trends}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis
                    dataKey="date"
                    tickFormatter={(value) => format(new Date(value), 'dd/MM', { locale: ptBR })}
                  />
                  <YAxis />
                  <Tooltip
                    labelFormatter={(value) => format(new Date(value), 'dd/MM/yyyy', { locale: ptBR })}
                  />
                  <Legend />
                  <Line
                    type="monotone"
                    dataKey="POSITIVO"
                    stroke={COLORS.POSITIVO}
                    strokeWidth={2}
                    name="Positivo"
                  />
                  <Line
                    type="monotone"
                    dataKey="NEGATIVO"
                    stroke={COLORS.NEGATIVO}
                    strokeWidth={2}
                    name="Negativo"
                  />
                  <Line
                    type="monotone"
                    dataKey="NEUTRAL"
                    stroke={COLORS.NEUTRAL}
                    strokeWidth={2}
                    name="Neutro"
                  />
                </LineChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Distribuição de Sentimentos</CardTitle>
              <CardDescription>
                Proporção de cada tipo de sentimento
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <PieChart>
                  <Pie
                    data={sentimentDistribution}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    onCellClick={(data) => fetchPostagensDetalhadas(data.name)}
                  >
                    {sentimentDistribution.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[entry.name]} />
                    ))}
                  </Pie>
                  <Tooltip />
                  <Legend />
                </PieChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Análise por Rede Social</CardTitle>
            <CardDescription>
              Comparação de sentimentos entre diferentes redes sociais
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={400}>
              <BarChart data={socialMediaStats}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="POSITIVO" fill={COLORS.POSITIVO} name="Positivo" />
                <Bar dataKey="NEGATIVO" fill={COLORS.NEGATIVO} name="Negativo" />
                <Bar dataKey="NEUTRAL" fill={COLORS.NEUTRAL} name="Neutro" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      <Dialog open={showPostagensModal} onOpenChange={(isOpen) => {
        setShowPostagensModal(isOpen);
        if (!isOpen) {
          setSelectedSentimentForList(null);
        }
      }}>
        <DialogContent className="max-w-2xl max-h-[90vh] flex flex-col">
          <DialogHeader className="p-6 pb-4 border-b">
            <DialogTitle>
              Postagens {modalPeriodTitle()} {selectedSentimentForList && `(${selectedSentimentForList})`}
            </DialogTitle>
            <DialogDescription>
              Lista detalhada das postagens para o período selecionado.
            </DialogDescription>
          </DialogHeader>

          <div className="flex-1 p-6 overflow-y-auto">
            {loadingPostagens ? (
              <div className="flex justify-center items-center h-40">
                <Loader2 className="h-8 w-8 animate-spin text-blue-500" />
                <span className="ml-2">Carregando postagens...</span>
              </div>
            ) : postagensDetalhadas.length === 0 ? (
              <p className="text-gray-600 text-center">Nenhuma postagem encontrada para este período.</p>
            ) : (
              <div className="space-y-4">
                {postagensDetalhadas.map((post) => (
                  <Card key={post.id} className="shadow-sm">
                    <CardContent className="p-4">
                      <p className="text-gray-800 text-base mb-2">{post.texto}</p>
                      <div className="flex flex-wrap justify-between items-center text-sm text-gray-600 gap-2">
                        <span>Rede: <span className="font-medium">{post.rede_social}</span></span>
                        <span>Sentimento: <span className={`font-semibold ${COLORS[post.sentimento]}`}>{post.sentimento}</span></span>
                        <span>{new Date(post.created_at).toLocaleDateString('pt-BR')}</span>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
          <DialogFooter className="p-4 border-t flex justify-end">
            <Button onClick={() => { setShowPostagensModal(false); setSelectedSentimentForList(null); }}>Fechar</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
    </>
  );
  
}
