import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Smile } from 'lucide-react';
import { useState } from 'react';

interface EmojiPickerProps {
    onInsert: (emoji: string) => void;
}

const emojis = [
    { emoji: '😀', name: 'sorriso feliz' },
    { emoji: '😃', name: 'sorriso grande' },
    { emoji: '😄', name: 'sorriso olhos' },
    { emoji: '😁', name: 'sorriso dentes' },
    { emoji: '😆', name: 'rindo' },
    { emoji: '😅', name: 'suor' },
    { emoji: '🤣', name: 'gargalhada' },
    { emoji: '😂', name: 'chorando rindo' },
    { emoji: '🙂', name: 'leve sorriso' },
    { emoji: '🙃', name: 'invertido' },
    { emoji: '😉', name: 'piscada' },
    { emoji: '😊', name: 'feliz' },
    { emoji: '😇', name: 'anjo' },
    { emoji: '🥰', name: 'apaixonado' },
    { emoji: '😍', name: 'amor' },
    { emoji: '🤩', name: 'estrelas' },
    { emoji: '😘', name: 'beijo' },
    { emoji: '😗', name: 'beijinho' },
    { emoji: '😚', name: 'beijo olhos' },
    { emoji: '😙', name: 'beijo sorriso' },
    { emoji: '😋', name: 'delicia' },
    { emoji: '😛', name: 'lingua' },
    { emoji: '😜', name: 'lingua piscada' },
    { emoji: '🤪', name: 'louco' },
    { emoji: '😝', name: 'lingua olhos' },
    { emoji: '🤑', name: 'dinheiro' },
    { emoji: '🤗', name: 'abraco' },
    { emoji: '🤭', name: 'riso' },
    { emoji: '🤫', name: 'silencio' },
    { emoji: '🤔', name: 'pensando' },
    { emoji: '🤐', name: 'boca fechada' },
    { emoji: '🤨', name: 'sobrancelha' },
    { emoji: '😐', name: 'neutro' },
    { emoji: '😑', name: 'sem expressao' },
    { emoji: '😶', name: 'sem boca' },
    { emoji: '😏', name: 'maroto' },
    { emoji: '😒', name: 'entediado' },
    { emoji: '🙄', name: 'revirando olhos' },
    { emoji: '😬', name: 'careta' },
    { emoji: '🤥', name: 'mentira' },
    { emoji: '😌', name: 'aliviado' },
    { emoji: '😔', name: 'pensativo' },
    { emoji: '😪', name: 'sonolento' },
    { emoji: '🤤', name: 'babando' },
    { emoji: '😴', name: 'dormindo' },
    { emoji: '😷', name: 'mascara' },
    { emoji: '🤒', name: 'termometro' },
    { emoji: '🤕', name: 'machucado' },
    { emoji: '🤢', name: 'enjoado' },
    { emoji: '🤮', name: 'vomitando' },
    { emoji: '🤧', name: 'espirrando' },
    { emoji: '🥵', name: 'calor' },
    { emoji: '🥶', name: 'frio' },
    { emoji: '🥴', name: 'tonto' },
    { emoji: '😵', name: 'zonzo' },
    { emoji: '🤯', name: 'mente explodindo' },
    { emoji: '🤠', name: 'cowboy' },
    { emoji: '🥳', name: 'festa' },
    { emoji: '😎', name: 'legal' },
    { emoji: '🤓', name: 'nerd' },
    { emoji: '🧐', name: 'monoculo' },
    { emoji: '😕', name: 'confuso' },
    { emoji: '😟', name: 'preocupado' },
    { emoji: '🙁', name: 'triste leve' },
    { emoji: '☹️', name: 'muito triste' },
    { emoji: '😮', name: 'surpreso' },
    { emoji: '😯', name: 'boca aberta' },
    { emoji: '😲', name: 'chocado' },
    { emoji: '😳', name: 'corado' },
    { emoji: '🥺', name: 'implorando' },
    { emoji: '😦', name: 'boca aberta triste' },
    { emoji: '😧', name: 'angustiado' },
    { emoji: '😨', name: 'com medo' },
    { emoji: '😰', name: 'ansioso suor' },
    { emoji: '😥', name: 'triste suor' },
    { emoji: '😢', name: 'chorando' },
    { emoji: '😭', name: 'chorando muito' },
    { emoji: '😱', name: 'gritando medo' },
    { emoji: '😖', name: 'confuso triste' },
    { emoji: '😣', name: 'perseverante' },
    { emoji: '😞', name: 'desapontado' },
    { emoji: '😓', name: 'suor frio' },
    { emoji: '😩', name: 'cansado' },
    { emoji: '😫', name: 'exausto' },
    { emoji: '🥱', name: 'bocejando' },
    { emoji: '😤', name: 'fumegando' },
    { emoji: '😡', name: 'bravo' },
    { emoji: '😠', name: 'zangado' },
    { emoji: '🤬', name: 'xingando' },
    { emoji: '😈', name: 'diabo sorrindo' },
    { emoji: '👋', name: 'acenando' },
    { emoji: '🤚', name: 'mao levantada' },
    { emoji: '🖐', name: 'mao aberta' },
    { emoji: '✋', name: 'mao' },
    { emoji: '🖖', name: 'vulcano' },
    { emoji: '👌', name: 'ok' },
    { emoji: '🤌', name: 'dedos juntos' },
    { emoji: '🤏', name: 'pouquinho' },
    { emoji: '✌️', name: 'vitoria' },
    { emoji: '🤞', name: 'dedos cruzados' },
    { emoji: '🤟', name: 'amo voce' },
    { emoji: '🤘', name: 'rock' },
    { emoji: '🤙', name: 'me liga' },
    { emoji: '👈', name: 'apontando esquerda' },
    { emoji: '👉', name: 'apontando direita' },
    { emoji: '👆', name: 'apontando cima' },
    { emoji: '👇', name: 'apontando baixo' },
    { emoji: '☝️', name: 'dedo levantado' },
    { emoji: '👍', name: 'joinha' },
    { emoji: '👎', name: 'joinha baixo' },
    { emoji: '✊', name: 'punho' },
    { emoji: '👊', name: 'punho batendo' },
    { emoji: '🤛', name: 'punho esquerdo' },
    { emoji: '🤜', name: 'punho direito' },
    { emoji: '👏', name: 'aplaudindo' },
    { emoji: '🙌', name: 'maos levantadas' },
    { emoji: '👐', name: 'maos abertas' },
    { emoji: '🤲', name: 'palmas juntas' },
    { emoji: '🤝', name: 'aperto mao' },
    { emoji: '🙏', name: 'rezando' },
    { emoji: '✍️', name: 'escrevendo' },
    { emoji: '💅', name: 'unhas' },
    { emoji: '🤳', name: 'selfie' },
    { emoji: '💪', name: 'musculo' },
    { emoji: '❤️', name: 'coracao vermelho' },
    { emoji: '🧡', name: 'coracao laranja' },
    { emoji: '💛', name: 'coracao amarelo' },
    { emoji: '💚', name: 'coracao verde' },
    { emoji: '💙', name: 'coracao azul' },
    { emoji: '💜', name: 'coracao roxo' },
    { emoji: '🖤', name: 'coracao preto' },
    { emoji: '🤍', name: 'coracao branco' },
    { emoji: '🤎', name: 'coracao marrom' },
    { emoji: '💔', name: 'coracao partido' },
    { emoji: '❣️', name: 'coracao exclamacao' },
    { emoji: '💕', name: 'dois coracoes' },
    { emoji: '💞', name: 'coracoes girando' },
    { emoji: '💓', name: 'coracao batendo' },
    { emoji: '💗', name: 'coracao crescendo' },
    { emoji: '💖', name: 'coracao brilhante' },
    { emoji: '💘', name: 'coracao flecha' },
    { emoji: '💝', name: 'coracao fita' },
    { emoji: '🔥', name: 'fogo' },
    { emoji: '⭐', name: 'estrela' },
    { emoji: '🌟', name: 'estrela brilhante' },
    { emoji: '✨', name: 'brilhos' },
    { emoji: '💫', name: 'tontura' },
    { emoji: '⚡', name: 'raio' },
    { emoji: '💥', name: 'explosao' },
    { emoji: '🌈', name: 'arco iris' },
    { emoji: '☀️', name: 'sol' },
    { emoji: '🌙', name: 'lua' },
    { emoji: '🌊', name: 'onda' },
    { emoji: '💧', name: 'gota' },
];

export function EmojiPicker({ onInsert }: EmojiPickerProps) {
    const [search, setSearch] = useState('');
    const [open, setOpen] = useState(false);

    const filteredEmojis = search ? emojis.filter((e) => e.name.toLowerCase().includes(search.toLowerCase()) || e.emoji.includes(search)) : emojis;

    const handleInsert = (emoji: string) => {
        onInsert(emoji);
        setOpen(false);
        setSearch('');
    };

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button type="button" variant="ghost" size="sm" className="text-muted-foreground -mr-5 md:mr-0">
                    <Smile size={16} />
                    <span className=" hidden md:inline">Emoji</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-84 mr-4">
                <div className="flex flex-col space-y-3">
                    <h4 className="text-foreground text-sm font-semibold">Escolha um Emoji</h4>
                    <input
                        type="text"
                        placeholder="Pesquisar emoji..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="bg-muted text-foreground placeholder:text-muted-foreground border-border focus:ring-ring w-full rounded-md border px-3 py-2 text-sm focus:ring-2 focus:outline-none"
                    />
                    <div className="max-h-[250px] overflow-y-auto">
                        <div className="grid grid-cols-10 gap-2">
                            {filteredEmojis.map((item, i) => (
                                <button
                                    key={i}
                                    type="button"
                                    onClick={() => handleInsert(item.emoji)}
                                    className="hover:bg-muted flex h-8 w-8 items-center justify-center rounded text-xl transition-colors"
                                    title={item.name}
                                >
                                    {item.emoji}
                                </button>
                            ))}
                        </div>
                        {filteredEmojis.length === 0 && <p className="text-muted-foreground py-8 text-center text-sm">Nenhum emoji encontrado</p>}
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}
