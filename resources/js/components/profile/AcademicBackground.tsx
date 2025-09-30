import InputError from '@/components/input-error';
import { ProfileCard } from '@/components/profile-card';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Option } from '@/components/ui/multiselect';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarIcon, CircleAlertIcon, GraduationCap, PlusIcon, TrashIcon } from 'lucide-react';
import * as React from 'react';
import { FormEvent } from 'react';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger
} from '@/components/ui/alert-dialog';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import profile from '@/routes/profile';
import { useAcademicBackground } from '@/stores/academicBackground';
import { AcademicBackground as AcademicBackgroundType } from '@/types';

interface AcademicBackgroundForm {
    institution: string;
    degree: string;
    start_date: Date;
    end_date?: Date | null;
    field_of_study: string;
    id: string | number;
}

const degrees: Option[] = [
    { value: 'Licenciatura', label: 'Licenciatura' },
    { value: 'Mestrado', label: 'Mestrado' },
    { value: 'Doutoramento', label: 'Doutoramento' },
    { value: 'Pós-graduação', label: 'Pós-graduação' },
    { value: 'Curso Técnico', label: 'Curso Técnico' },
    { value: 'Curso de Formação', label: 'Curso de Formação' },
    { value: 'Certificação', label: 'Certificação' },
    { value: 'Outro', label: 'Outro' },
];

export function AcademicBackground({ academicBackgrounds }: { academicBackgrounds: AcademicBackgroundType[] }) {
    const { toast } = useToast();

    const { isOpen, set } = useAcademicBackground();
    const [startDateOpen, setStartDateOpen] = React.useState(false);
    const [endDateOpen, setEndDateOpen] = React.useState(false);

    const {
        data,
        setData,
        post,
        delete: destroy,
        errors,
        processing,
        reset,
    } = useForm<Required<AcademicBackgroundForm>>({
        id: '',
        institution: '',
        degree: '',
        start_date: new Date(),
        end_date: null,
        field_of_study: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post(profile.education().url, {
            preserveScroll: true,
            only: ['academicBackgrounds'],
            onSuccess: () => {
                toast({
                    title: 'Sucesso!',
                    description: 'Formação académica adicionada com sucesso.',
                });
                reset();
                set(false);
            },
        });
    }

    function deleteEducation(id: number) {
        destroy(profile.education.delete(id).url, {
            preserveScroll: true,
            replace: true,
            only: ['academicBackgrounds'],
            onSuccess: () => {
                toast({
                    title: 'Sucesso!',
                    description: 'Formação académica eliminada com sucesso.',
                });
            },
        });
    }

    return (
        <ProfileCard title="Formação Académica" icon={<PlusIcon />} onClick={() => set(true)}>
            {academicBackgrounds.length === 0 && (
                <>
                    <GraduationCap />
                    <p className="mb-4 max-w-100 text-center">Destaque sua formação acadêmica e conquiste o reconhecimento que você merece!</p>
                </>
            )}
            <div
                className={cn(
                    'grid w-full grid-cols-1 gap-4',
                    academicBackgrounds.length > 1 ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1' /* Add your custom styles here */,
                )}
            >
                {academicBackgrounds.map((education) => (
                    <Card className="bg-card w-full items-start p-4" key={education.id}>
                        <CardTitle className="flex w-full items-center gap-1 text-sm font-semibold">
                            <GraduationCap /> {education.degree}
                            <div className="flex-1" />
                            <AlertDialog>
                                <AlertDialogTrigger asChild>
                                    <TrashIcon className="text-red-500" size={20} />
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <div className="flex flex-col gap-2 max-sm:items-center sm:flex-row sm:gap-4">
                                        <div className="flex size-9 shrink-0 items-center justify-center rounded-full border" aria-hidden="true">
                                            <CircleAlertIcon className="cursor-pointer opacity-80" size={16} />
                                        </div>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Eliminar Formação ?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Tem a certeza que deseja eliminar esta formação académica? Esta ação não pode ser desfeita.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                    </div>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction onClick={() => deleteEducation(education.id)}>Confirm</AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </CardTitle>
                        <CardContent className="-mt-2 grid w-full grid-cols-1 items-center gap-4 border-t-1 pt-4 md:grid-cols-2">
                            <div className="flex flex-col items-start justify-start">
                                <small className="text-xs text-gray-500">Instituíção</small>
                                <span className="bold text-sm">{education.institution}</span>
                            </div>
                            <div className="flex flex-col items-start justify-start">
                                <small className="text-xs text-gray-500">Area de Estudo</small>
                                <span className="bold text-sm">{education.field_of_study}</span>
                            </div>
                            <div className="flex flex-col items-start justify-start">
                                <small className="text-xs text-gray-500">Inicio</small>
                                <span className="bold text-sm">{format(new Date(education.start_date), 'dd-MM-yyyy')}</span>
                            </div>
                            {education.end_date && (
                                <div className="flex flex-col items-start justify-start">
                                    <small className="text-xs text-gray-500">Fim</small>
                                    <span className="bold text-sm">{format(new Date(education.end_date), 'dd-MM-yyyy')}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                ))}
            </div>
            <Dialog open={isOpen} onOpenChange={set}>
                <DialogTrigger asChild onClick={() => set(true)}>
                    <Button>
                        <GraduationCap />
                        Adicionar Formação Académica
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader className="space-y-2">
                        <DialogTitle>Formação Académica</DialogTitle>
                        <DialogDescription>
                            Adicione sua formação acadêmica ou outra formação relevante que você gostaria de compartilhar com os outros.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="mt-4 space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="institution">Instituíção *</Label>
                            <Input
                                id="institution"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="Instituição"
                                value={data.institution}
                                onChange={(e) => setData('institution', e.target.value)}
                                placeholder="Ex. Universidade de Cabo Verde"
                                className="placeholder:text-muted"
                            />
                            <InputError message={errors.institution} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="field_of_study">Area de Estudo *</Label>
                            <Input
                                id="field_of_study"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="Instituição"
                                value={data.field_of_study}
                                onChange={(e) => setData('field_of_study', e.target.value)}
                                placeholder="Ex. Engenharia Informática"
                                className="placeholder:text-muted"
                            />
                            <InputError message={errors.field_of_study} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="degree">Grau Académico *</Label>
                            <Select onValueChange={(value) => setData('degree', value)} defaultValue={data.degree}>
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Selecione o grau acadêmico" />
                                </SelectTrigger>
                                <SelectContent>
                                    {degrees.map((degree) => (
                                        <SelectItem key={degree.value} value={degree.value}>
                                            {degree.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.degree} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="start_date">Data de Início *</Label>
                            <Popover open={startDateOpen} onOpenChange={setStartDateOpen}>
                                <PopoverTrigger asChild>
                                    <Button
                                        id="start_date"
                                        variant={'outline'}
                                        className={cn(
                                            'group bg-background hover:bg-background border-input w-full justify-between px-3 font-normal outline-offset-0 outline-none focus-visible:outline-[3px]',
                                            !data.start_date && 'text-muted-foreground',
                                        )}
                                    >
                                        <span className={cn('truncate', !data.start_date && 'text-muted-foreground')}>
                                            {data.start_date ? format(data.start_date, 'dd-MM-yyyy') : 'Selecione a data de início'}
                                        </span>
                                        <CalendarIcon
                                            size={16}
                                            className="text-muted-foreground/80 group-hover:text-foreground shrink-0 transition-colors"
                                            aria-hidden="true"
                                        />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto overflow-hidden p-2" align="start" forceMount>
                                    <Calendar
                                        className="bg-background min-h-[360px] w-[300px] rounded-md p-3"
                                        mode="single"
                                        captionLayout="dropdown"
                                        selected={data.start_date}
                                        onSelect={(date) => {
                                            setData('start_date', date ? date : new Date());
                                            setStartDateOpen(false);
                                        }}
                                    />
                                </PopoverContent>
                            </Popover>
                            <InputError message={errors.start_date} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="start_date">Data de Fim</Label>
                            <Popover open={endDateOpen} onOpenChange={setEndDateOpen}>
                                <PopoverTrigger asChild>
                                    <Button
                                        id="end_date"
                                        variant={'outline'}
                                        className={cn(
                                            'group bg-background hover:bg-background border-input w-full justify-between px-3 font-normal outline-offset-0 outline-none focus-visible:outline-[3px]',
                                            !data.end_date && 'text-muted-foreground',
                                        )}
                                    >
                                        <span className={cn('truncate', !data.end_date && 'text-muted-foreground')}>
                                            {data.end_date ? format(data.end_date, 'dd-MM-yyyy') : 'Selecione a data de fim'}
                                        </span>
                                        <CalendarIcon
                                            size={16}
                                            className="text-muted-foreground/80 group-hover:text-foreground shrink-0 transition-colors"
                                            aria-hidden="true"
                                        />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-full p-2" align="start" forceMount>
                                    <Calendar
                                        className="bg-background min-h-[360px] w-[300px] rounded-md p-3"
                                        captionLayout="dropdown"
                                        mode="single"
                                        selected={data.end_date ? data.end_date : undefined}
                                        onSelect={(date) => {
                                            setData('end_date', date || null);
                                            setEndDateOpen(false);
                                        }}
                                    />
                                </PopoverContent>
                            </Popover>
                            <InputError message={errors.end_date} />
                        </div>
                    </form>
                    <div className="flex flex-col sm:flex-row sm:justify-end">
                        <Button type="button" onClick={submit} disabled={processing}>
                            <GraduationCap />
                            Guardar
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </ProfileCard>
    );
}
