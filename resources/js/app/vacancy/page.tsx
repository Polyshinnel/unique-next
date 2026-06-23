import type { Metadata } from 'next';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { Anchor, Container, Image, SimpleGrid, Stack, Text, Title } from '@mantine/core';

export const metadata: Metadata = {
    title: 'Вакансии | ЮНИК С',
    description: 'Актуальные вакансии компании ЮНИК С и условия сотрудничества для региональных представителей.',
};

const interestList = [
    'если вам интересно работать с оборудованием',
    'если вы чувствуете промышленный потенциал вашего региона',
    'если вам интересны условия, описные ниже',
] as const;

const benefitsList = [
    'Работа по совместительству или частичная занятость;',
    'Адекватную и понятную систему расчёта ЗП (сдельная);',
    'ЗП – фиксированный процент с каждой сделки (%);',
    'Свободный график работы и совместительство;',
    'Возможность самому строить и планировать рабочий процесс;',
    'Компенсация бензина.',
] as const;

const responsibilitiesList = [
    'Выезд на производственные площадки для проведения описей или отгрузок оборудования;',
    'Общение с поставщиками и клиентами, представление интересов компании;',
    'Проведение технических осмотров единиц оборудования, проведение фото и видео съемки, работа с чек-листами (обязательно наличие смартфона с «нормальной» камерой!!);',
    'Работа с облачными хранилищами для выгрузки фото и видео материалов, а также с гугл-таблицами (наличие собственного ПК обязательно!!);',
    'Организация показов оборудования потенциальным клиентам;',
    'Организация отгрузок оборудования;',
    'Работа с отгрузочным документами;',
    'При необходимости работа с подрядчиками (погрузка-разгрузка, демонтаж, такелажные работы), поиск подрядчиков, согласование условий, сроков и стоимости работ;',
    'Максимально четкое исполнение работ в оговоренные сроки;',
    'Оперативная передача и обмен информацией с ответственными менеджерами.',
] as const;

const professionalRequirementsList = [
    'Техническая грамотность, техническое образование или практический производственный опыт будут являться преимуществом;',
    'Отличных знаний оборудования не требуется, основным является тех. грамотность и желание разобраться. Но производственный опыт или профильное образование будет являться преимуществом.',
    'Высокая коммуникабельности и умение общаться с клиентами, умение вести переговоры и договариваться, представлять лицо компании перед клиентом и поставщиком.',
] as const;

const personalRequirementsList = [
    'Наличие личного ПК;',
    'Наличие смартфона с хорошей камерой;',
    'Уверенный пользователь ПК;',
    'Работа разъездного характера, КОМПАНИЯ АВТОМОБИЛЬ НЕ ПРЕДОСТАВЛЯЕТ!',
    'Уметь согласовывать и выполнять сроки;',
    'Быть ответственным и целеустремленным;',
    'Коммуникабельным, позитивным и гибким;',
    'Уметь и любить постоянно развиваться и учиться.',
] as const;

function BulletList({ items }: { items: readonly string[] }) {
    return (
        <SimpleGrid cols={1} spacing="md">
            {items.map((item) => (
                <div key={item} className="sales-list-item">
                    <span className="sales-list-item__dot" />
                    <Text>{item}</Text>
                </div>
            ))}
        </SimpleGrid>
    );
}

export default function VacancyPage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero about-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <Link href="/about">О компании</Link>
                            <span>/</span>
                            <span>Вакансии</span>
                        </div>
                        <Stack gap="lg" maw={840}>
                            <Title order={1}>Вакансии</Title>
                            <Text size="lg">Актуальные вакансии нашей компании</Text>
                        </Stack>
                    </Container>
                </section>

                <section className="content-section">
                    <Container size="xl">
                        <div className="vacancy-intro">
                            <Stack gap="lg">
                                <Title order={2}>Ищем компетенции</Title>
                                <Text size="lg" c="dimmed">
                                    Мы постоянно развиваемся, поэтому всегда открыты к любым возможностям для начала
                                    работы в новых регионах.
                                </Text>
                                <Text size="lg" c="dimmed">
                                    Мы заинтересованы в компетентных сотрудниках и готовы рассматривать подходящие
                                    кандидатуры на различные позиции в нашей компании.
                                </Text>
                            </Stack>

                            <div className="vacancy-intro__image">
                                <Image src="/assets/img/vacancy-img.jpeg" alt="Вакансии компании ЮНИК С" />
                            </div>
                        </div>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="xl">
                        <Stack gap="xl">
                            <Title order={2}>Список актуальных вакансий</Title>

                            <article className="vacancy-card">
                                <Stack gap="xl">
                                    <Stack gap="lg">
                                        <Title order={3} className="vacancy-card__title">
                                            Сотрудник на должность регионального представителя для организации
                                            отгрузок промышленного оборудования.
                                        </Title>
                                        <BulletList items={interestList} />
                                    </Stack>

                                    <Stack gap="lg">
                                        <Text className="vacancy-card__subtitle">Мы предлагаем:</Text>
                                        <BulletList items={benefitsList} />
                                    </Stack>

                                    <Stack gap="lg">
                                        <Text className="vacancy-card__subtitle">Обязанности сотрудника:</Text>
                                        <BulletList items={responsibilitiesList} />
                                    </Stack>

                                    <Stack gap="lg">
                                        <Text className="vacancy-card__subtitle">
                                            Профессиональные требования к соискателю:
                                        </Text>
                                        <BulletList items={professionalRequirementsList} />
                                    </Stack>

                                    <Stack gap="lg">
                                        <Text className="vacancy-card__subtitle">Личностные требования к соискателю:</Text>
                                        <BulletList items={personalRequirementsList} />
                                    </Stack>

                                    <Text component="p" fs="italic" className="vacancy-card__note">
                                        ❗Данная вакансия для технически грамотного человека, человека со свободным
                                        графиком работы, с возможностью совместительства. С хорошими техническими
                                        знаниями, умеющий легко находить общий язык с людьми, с нормальными
                                        организаторскими качествами, позволяющими быстро решать рабочие вопросы. Не
                                        боящегося испачкать руки, готового к свободным перемещениям в рамках региона.
                                    </Text>
                                </Stack>
                            </article>
                        </Stack>
                    </Container>
                </section>

                <section className="content-section">
                    <Container size="md">
                        <div className="vacancy-contact-card">
                            <Stack gap="md" ta="center">
                                <Text size="lg">
                                    Направляйте ваше резюме на нашу почту:{' '}
                                    <Anchor href="mailto:CV@UNIQSET.COM" fw={700}>
                                        CV@UNIQSET.COM
                                    </Anchor>
                                </Text>
                                <Text size="lg">Мы обязательно рассмотрим резюме и свяжемся с вами</Text>
                            </Stack>
                        </div>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
